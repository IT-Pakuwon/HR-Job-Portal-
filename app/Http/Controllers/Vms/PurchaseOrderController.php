<?php
namespace App\Http\Controllers\Vms;

use App\Http\Controllers\Controller;
use App\Models\SysMenu;
use App\Models\Vms\PurchaseOrder;
use App\Models\Vms\PurchaseContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PurchaseOrderController extends Controller
{
    public const TYPES = ['po'=>'Purchase Order (PO)', 'spk'=>'Surat Perintah Kerja (SPK)', 'kontrak'=>'Kontrak'];

    private function companies(Request $request)
    {
        $user=$request->user();
        abort_unless($user,403);
        $admin=in_array('admin',$user->roles(),true);
        abort_unless($admin || SysMenu::allowedScreenKeysForUsername($user->username)->contains('POVMS|VMS'),403);
        $query=DB::connection('mysql7')->table('company');
        if(!$admin) {
            $query->whereIn('CpnyID',DB::connection('mysql7')->table('admin_company as ac')
                ->join('admin as a','a.id','=','ac.UserID')->where('a.username',$user->username)->where('a.status',1)->select('ac.CpnyID'));
        }
        return $query->orderBy('CpnyID')->get(['CpnyID','CpnyName']);
    }

    private function base(string $type, array $companyIds, array $filters=[])
    {
        $query=$type==='kontrak'?PurchaseContract::query():PurchaseOrder::query()->where('material_service',$type==='po'?'Purchase Material':'Purchase Service')->where('podate','>=','2022-01-01');
        $query->whereIn('cpny_id',$companyIds);
        if(!empty($filters['company']))$query->where('cpny_id',$filters['company']);
        if(!empty($filters['vendor']))$query->where('vendor_id',$filters['vendor']);
        return $query;
    }

    private function withStatus($query,string $type)
    {
        $db=DB::connection('mysql7');
        $table=$type==='kontrak'?'staging_kontrak':'staging_po';
        $query->select($table.'.*')->addSelect(['purchaser_name'=>$db->table('admin')->select('name')->whereColumn('idacum',$table.'.purchaser')->orderBy('id')->limit(1)]);
        if($type==='kontrak') {
            return $query->addSelect(['document_status'=>$db->table('orderheaderkontrak')->select('status')->whereColumn('id_company',$table.'.cpny_id')->whereColumn('second_vendor_id',$table.'.vendor_id')->whereColumn('id_kontrak',$table.'.kontrakid')->orderByDesc('id')->limit(1)]);
        }
        return $query->addSelect([
            'ordered_qty'=>$db->table('staging_po_detail')->selectRaw('SUM(qty)')->whereColumn('cpny_id',$table.'.cpny_id')->whereColumn('ponbr',$table.'.ponbr'),
            'received_qty'=>$db->table('staging_receipt_detail')->selectRaw('SUM(receiptqty)')->whereColumn('cpny_id',$table.'.cpny_id')->whereColumn('ponbr',$table.'.ponbr'),
            'document_status'=>$db->table('orderheader')->select('Status')->whereColumn('CpnyID',$table.'.cpny_id')->whereColumn('SecVendorID',$table.'.vendor_id')->whereColumn('OrderNo',$table.'.ponbr')->orderByDesc('id')->limit(1),
        ]);
    }

    public function index(Request $request)
    {
        $companies=$this->companies($request);$ids=$companies->pluck('CpnyID')->all();
        $filters=$request->validate(['type'=>['nullable',Rule::in(array_keys(self::TYPES))],'company'=>['nullable',Rule::in($ids)],'vendor'=>['nullable','string','max:50'],'search'=>['nullable','string','max:100'],'per_page'=>['nullable','integer','in:10,25,50,100'],'page'=>['nullable','integer','min:1']]);
        $type=$filters['type']??($request->hasSession()?$request->session()->get('vms.purchase-orders.type','po'):'po');
        if(!isset(self::TYPES[$type]))$type='po';
        if($request->hasSession())$request->session()->put('vms.purchase-orders.type',$type);
        $types=self::TYPES;$counts=[];
        foreach($types as $key=>$label)$counts[$key]=$this->base($key,$ids,$filters)->count();
        $query=$this->withStatus($this->base($type,$ids,$filters),$type);
        if(filled($filters['search']??null))$query->where(function($q)use($type,$filters){
            $fields=$type==='kontrak'?['kontrakid','nosk','nopklegal','vendorname','vendor_id','purchaser','department_id']:['ponbr','vendorname','vendor_id','purchaser'];
            foreach($fields as $field)$q->orWhere($field,'like','%'.$filters['search'].'%');
        });
        $orders=$query->orderByDesc($type==='kontrak'?'kontrakdate':'podate')->orderByDesc('id')->paginate($filters['per_page']??10)->withQueryString();
        $vendors=DB::connection('mysql7')->query()->fromSub(
            DB::connection('mysql7')->table('staging_po')->whereIn('cpny_id',$ids)->select('vendor_id','vendorname')->union(DB::connection('mysql7')->table('staging_kontrak')->whereIn('cpny_id',$ids)->select('vendor_id','vendorname')),'v'
        )->whereNotNull('vendor_id')->orderBy('vendorname')->get()->unique('vendor_id');
        return response()->view('pages.vms.purchase-order.index',compact('companies','vendors','filters','types','type','counts','orders'))->header('Cache-Control','no-store, private');
    }

    public function show(Request $request,string $type,int $id)
    {
        abort_unless(isset(self::TYPES[$type]),404);
        $companies=$this->companies($request);
        $order=$this->withStatus($this->base($type,$companies->pluck('CpnyID')->all()),$type)->findOrFail($id);
        $lines=$type==='kontrak'?collect():DB::connection('mysql7')->table('staging_po_detail')->where('cpny_id',$order->cpny_id)->where('ponbr',$order->ponbr)->orderBy('linenbr')->get(['linenbr','inventoryid','inventory_descr','qty','uom','unitcost','taxamt','totalcost']);
        return response()->view('pages.vms.purchase-order.detail',compact('order','type','lines'))->header('Cache-Control','no-store, private');
    }
}
