<?php
namespace App\Http\Controllers\Vms;
use App\Http\Controllers\Controller;
use App\Models\SysMenu;
use App\Services\Vms\InvoiceRegisterDirectory;
use App\Services\Vms\InvoiceRegisterWorkflow;
use App\Services\Vms\VendorProfileWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InvoiceRegisterController extends Controller {
    private function companies(Request $request){
        $user=$request->user();abort_unless($user,403);$admin=in_array('admin',$user->roles(),true);
        abort_unless($admin||SysMenu::allowedScreenKeysForUsername($user->username)->contains('IRVMS|VMS'),403);
        $q=DB::connection('mysql7')->table('company');if(!$admin)$q->whereIn('CpnyID',DB::connection('mysql7')->table('admin_company as c')->join('admin as a','a.id','=','c.UserID')->where('a.username',$user->username)->where('a.status',1)->select('c.CpnyID'));
        return $q->orderBy('CpnyID')->get(['CpnyID','CpnyName']);
    }
    public function index(Request $request,InvoiceRegisterDirectory $directory){
        $companies=$this->companies($request);$ids=$companies->pluck('CpnyID')->all();
        $filters=$request->validate(['tab'=>['nullable',Rule::in(array_keys(InvoiceRegisterDirectory::TABS))],'company'=>['nullable',Rule::in($ids)],'vendor'=>['nullable','integer','min:1'],'business_unit'=>['nullable','string','max:50'],'status'=>['nullable',Rule::in(['except_reject','all','P','user_inprogress','H','D','A','R','L','not_send','approve','return'])],'search'=>['nullable','string','max:100'],'per_page'=>['nullable','integer','in:10,25,50,100'],'page'=>['nullable','integer','min:1']]);
        $tab=$filters['tab']??($request->hasSession()?$request->session()->get('vms.invoice-register.tab','review'):'review');if(!isset(InvoiceRegisterDirectory::TABS[$tab]))$tab='review';if($request->hasSession())$request->session()->put('vms.invoice-register.tab',$tab);
        $valid=$tab==='completed'?['all','not_send','approve','return']:['except_reject','all','P','user_inprogress','H','D','A','R','L'];if(!in_array($filters['status']??'', $valid,true))$filters['status']=$valid[0];
        $tabs=InvoiceRegisterDirectory::TABS;$counts=$directory->counts($ids);
        $invoices=$directory->paginate($tab==='scan'&&blank($filters['search']??null)?[]:$ids,$tab,$filters,(int)($filters['per_page']??10),(int)($filters['page']??1))->withQueryString();
        $stats=$directory->statistics($invoices);
        $vendors=$tab==='scan'?collect():$directory->vendors($ids);
        $businessUnits=$tab==='scan'?collect():DB::connection('mysql7')->query()->fromSub(DB::connection('mysql7')->table('staging_po')->whereIn('cpny_id',$ids)->select('business_unit_id')->union(DB::connection('mysql7')->table('staging_kontrak')->whereIn('cpny_id',$ids)->select('business_unit_id')),'units')->whereNotNull('business_unit_id')->where('business_unit_id','!=','')->orderBy('business_unit_id')->pluck('business_unit_id');
        return response()->view($request->ajax()?'pages.vms.invoice-register.content':'pages.vms.invoice-register.index',compact('companies','filters','tabs','tab','counts','invoices','stats','vendors','businessUnits'))->header('Cache-Control','no-store, private');
    }
    public function show(Request $request,InvoiceRegisterDirectory $directory,VendorProfileWorkflow $actors,string $kind,int $id){
        $invoice=$directory->find($this->companies($request)->pluck('CpnyID')->all(),$kind,$id);$actor=$actors->actor($request->user());
        return response()->view('pages.vms.invoice-register.detail',$directory->detail($invoice)+compact('actor'))->header('Cache-Control','no-store, private');
    }
    public function decide(Request $request,InvoiceRegisterDirectory $directory,InvoiceRegisterWorkflow $workflow,VendorProfileWorkflow $actors,string $kind,int $id){
        $companies=$this->companies($request)->pluck('CpnyID')->all();
        $data=$request->validate(['action'=>['required',Rule::in(['approve','revise','reject','final','receive','return'])],'document'=>['nullable','integer','min:1'],'tax_invoice'=>['nullable','string','max:100'],'note'=>['nullable','string','max:5000'],'token'=>['required','string','size:64']]);
        $workflow->decide($directory,$companies,$kind,$id,$actors->actor($request->user()),$data);
        return response()->json(['message'=>'Invoice register berhasil diperbarui.']);
    }
}
