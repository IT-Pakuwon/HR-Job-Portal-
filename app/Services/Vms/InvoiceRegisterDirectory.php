<?php
namespace App\Services\Vms;
use App\Models\Vms\InvoiceRegister;
use App\Models\Vms\ContractInvoiceRegister;
use Illuminate\Support\Facades\DB;

class InvoiceRegisterDirectory {
    public const TABS=['review'=>'Invoice Register Review','completed'=>'Invoice Register Completed','scan'=>'Scan Invoice Register'];
    public const STATUSES=['P'=>'In Progress','H'=>'Hold','D'=>'Revised','R'=>'Rejected','A'=>'Approved (Final Review Finance)','C'=>'Completed','L'=>'Cancel'];
    public function db(){return DB::connection('mysql7');}
    public function query(array $companies){
        $po=$this->db()->table('ordconfirmhdr as h')->whereIn('h.CpnyID',$companies)->where('h.step',5)->selectRaw("h.id, 'po' as kind, h.CpnyID as company, h.OrdConfID as number, h.OrdConfDate as date, h.OrderNo as order_number, h.VendorID as vendor_id, h.VendorName as vendor_name, h.SecVendorID as second_vendor_id, h.Module as module, h.Status as status, h.ProformaStatusApproval as send_status, h.SubTotal as subtotal, h.Tax as tax, h.Total as total, h.InvoiceDate as invoice_date, h.note, h.financenote");
        $contract=$this->db()->table('ordconfirmhdrkontrak as h')->leftJoin('vendor as v','v.vendor_id','=','h.id_vendor')->whereIn('h.id_company',$companies)->where('h.step',5)->selectRaw("h.id, 'kontrak' as kind, h.id_company as company, h.ord_conf_id as number, h.ord_conf_date as date, h.id_kontrak as order_number, h.id_vendor as vendor_id, v.vendor_nama as vendor_name, h.second_vendor_id, h.module, h.status, h.proforma_status_approval as send_status, h.sub_total as subtotal, h.tax, h.total, h.invoice_date, h.note, h.financenote");
        return $this->db()->query()->fromSub($po->unionAll($contract),'invoices');
    }
    public function filter($query,string $tab,array $filters=[]){
        if($tab==='review'){
            $query->where('status','!=','C');
            $status=$filters['status']??'except_reject';
            if($status==='except_reject')$query->where('status','!=','R');
            elseif($status!=='all')$query->where('status',$status==='user_inprogress'?'P':$status);
            if(!in_array($status,['all','user_inprogress'],true))$query->where(function($q)use($status){$q->where('kind','!=','kontrak')->orWhereExists(function($s)use($status){$s->selectRaw('1')->from('ordconfirmkontrak as billing')->whereColumn('billing.id_ordconfirmhdrkontrak','invoices.id')->where('billing.status',in_array($status,['R','D'],true)?$status:'C');});});
            if($status==='user_inprogress')$query->where('kind','kontrak')->whereExists(function($q){$q->selectRaw('1')->from('ordconfirmkontrak as billing')->whereColumn('billing.id_ordconfirmhdrkontrak','invoices.id')->where('billing.status','P');});
        }elseif($tab==='completed'){
            $query->where('status','C');$status=$filters['status']??'all';if($status!=='all')$query->where('send_status',$status);
        }
        foreach(['company','vendor_id'=>'vendor'] as $column=>$input){if(is_int($column))$column=$input;if(filled($filters[$input]??null))$query->where($column,$filters[$input]);}
        if(filled($filters['business_unit']??null))$query->where(function($q)use($filters){
            foreach(['po'=>['staging_po','ponbr'],'kontrak'=>['staging_kontrak','kontrakid']] as $kind=>[$table,$number])$q->orWhere(function($q)use($kind,$table,$number,$filters){$q->where('kind',$kind)->whereExists(fn($s)=>$s->selectRaw('1')->from($table.' as stage')->whereColumn('stage.cpny_id','invoices.company')->whereColumn('stage.'.$number,'invoices.order_number')->where('stage.business_unit_id',$filters['business_unit']));});
        });
        if(filled($filters['search']??null))$query->where(function($q)use($filters){foreach(['number','order_number','vendor_name','second_vendor_id'] as $column)$q->orWhere($column,'like','%'.$filters['search'].'%');});
        return $query;
    }
    /** Apply filters to each source before UNION, so MySQL never materializes all invoices. */
    private function listingBranches(array $companies,string $tab,array $filters=[],?int $limit=null){
        $branches=[];
        foreach(['po','kontrak'] as $kind){
            $contract=$kind==='kontrak';
            $c=$contract?['company'=>'id_company','number'=>'ord_conf_id','date'=>'ord_conf_date','order_number'=>'id_kontrak','vendor_id'=>'id_vendor','second_vendor_id'=>'second_vendor_id','module'=>'module','status'=>'status','send_status'=>'proforma_status_approval']:['company'=>'CpnyID','number'=>'OrdConfID','date'=>'OrdConfDate','order_number'=>'OrderNo','vendor_id'=>'VendorID','second_vendor_id'=>'SecVendorID','module'=>'Module','status'=>'Status','send_status'=>'ProformaStatusApproval'];
            $q=$this->db()->table(($contract?'ordconfirmhdrkontrak':'ordconfirmhdr').' as h')->whereIn('h.'.$c['company'],$companies)->where('h.step',5);
            if($contract)$q->leftJoin('vendor as v','v.vendor_id','=','h.id_vendor');
            if($tab==='review'){
                $status=$filters['status']??'except_reject';$q->where('h.'.$c['status'],'!=','C');
                if($status==='except_reject')$q->where('h.'.$c['status'],'!=','R');elseif($status!=='all')$q->where('h.'.$c['status'],$status==='user_inprogress'?'P':$status);
                if($status==='user_inprogress'&&!$contract)$q->whereRaw('1=0');
                if($contract&&$status!=='all')$q->whereExists(fn($s)=>$s->selectRaw('1')->from('ordconfirmkontrak as billing')->whereColumn('billing.id_ordconfirmhdrkontrak','h.id')->where('billing.status',$status==='user_inprogress'?'P':(in_array($status,['R','D'],true)?$status:'C')));
            }elseif($tab==='completed'){
                $q->where('h.'.$c['status'],'C');if(($filters['status']??'all')!=='all')$q->where('h.'.$c['send_status'],$filters['status']);
            }
            foreach(['company'=>'company','vendor'=>'vendor_id'] as $input=>$field)if(filled($filters[$input]??null))$q->where('h.'.$c[$field],$filters[$input]);
            if(filled($filters['business_unit']??null))$q->whereExists(fn($s)=>$s->selectRaw('1')->from(($contract?'staging_kontrak':'staging_po').' as stage')->whereColumn('stage.cpny_id','h.'.$c['company'])->whereColumn('stage.'.($contract?'kontrakid':'ponbr'),'h.'.$c['order_number'])->where('stage.business_unit_id',$filters['business_unit']));
            if(filled($filters['search']??null))$q->where(function($search)use($contract,$c,$filters){foreach(['number','order_number','second_vendor_id'] as $field)$search->orWhere('h.'.$c[$field],'like','%'.$filters['search'].'%');$search->orWhere($contract?'v.vendor_nama':'h.VendorName','like','%'.$filters['search'].'%');});
            $select=['h.id'];foreach($c as $alias=>$field)$select[]='h.'.$field.' as '.$alias;
            $q->select($select)->selectRaw("'".$kind."' as kind")->addSelect(($contract?'v.vendor_nama':'h.VendorName').' as vendor_name');if($limit!==null)$q->orderByDesc('h.'.$c['date'])->orderByDesc('h.id')->limit($limit);$branches[]=$q;
        }
        return $branches;
    }
    public function listingQuery(array $companies,string $tab,array $filters=[]){
        $branches=$this->listingBranches($companies,$tab,$filters);return $this->db()->query()->fromSub($branches[0]->unionAll($branches[1]),'invoices');
    }
    public function paginate(array $companies,string $tab,array $filters=[],int $perPage=10,int $page=1){
        $total=0;foreach($this->listingBranches($companies,$tab,$filters) as $branch)$total+=$branch->count();
        $items=collect();
        if($total>0&&$page<=ceil($total/$perPage)){
            $branches=$this->listingBranches($companies,$tab,$filters,$page*$perPage);
            $items=$this->db()->query()->fromSub($branches[0]->unionAll($branches[1]),'invoices')->orderByDesc('date')->orderByDesc('id')->orderBy('kind')->offset(($page-1)*$perPage)->limit($perPage)->get();
        }
        return new \Illuminate\Pagination\LengthAwarePaginator($items,$total,$perPage,$page,['path'=>\Illuminate\Pagination\Paginator::resolveCurrentPath()]);
    }
    public function counts(array $companies):array {
        $counts=['review'=>0,'completed'=>0];
        foreach([['ordconfirmhdr','CpnyID','Status'],['ordconfirmhdrkontrak','id_company','status']] as [$table,$company,$status]){
            $row=$this->db()->table($table)->whereIn($company,$companies)->where('step',5)->selectRaw("SUM(CASE WHEN $status = 'C' THEN 1 ELSE 0 END) as completed, SUM(CASE WHEN $status != 'C' THEN 1 ELSE 0 END) as review")->first();
            foreach($counts as $key=>$value)$counts[$key]+=(int)$row->{$key};
        }
        return $counts;
    }
    public function vendors(array $companies){
        $ids=$this->db()->table('ordconfirmhdr')->whereIn('CpnyID',$companies)->where('step',5)->select('VendorID as vendor_key')->union($this->db()->table('ordconfirmhdrkontrak')->whereIn('id_company',$companies)->where('step',5)->select('id_vendor as vendor_key'));
        return $this->db()->table('vendor')->whereIn('vendor_id',$this->db()->query()->fromSub($ids,'vendor_ids')->select('vendor_key'))->orderBy('vendor_nama')->get(['vendor_id','vendor_nama','second_vendor_id']);
    }
    public function statistics($invoices):array {
        $stats=[];foreach($invoices as $invoice)$stats[$invoice->kind.':'.$invoice->id]=['tax'=>['W'=>0,'R'=>0,'A'=>0],'finance'=>['W'=>0,'R'=>0,'A'=>0]];
        foreach(['po','kontrak'] as $kind){
            $ids=collect($invoices->items())->where('kind',$kind)->pluck('id')->all();if(!$ids)continue;$contract=$kind==='kontrak';
            $query=$this->db()->table(($contract?'ordconfirmattachkontrak':'ordconfirmattach').' as a')->join(($contract?'ordconfirmhdrkontrak':'ordconfirmhdr').' as h',function($join)use($contract){
                $join->on($contract?'a.ord_conf_id':'a.OrdConfID','=',$contract?'h.ord_conf_id':'h.OrdConfID')->on($contract?'a.id_company':'a.CpnyID','=',$contract?'h.id_company':'h.CpnyID')->on('a.vendor_id','=',$contract?'h.id_vendor':'h.VendorID')->on($contract?'a.id_kontrak':'a.OrderNo','=',$contract?'h.id_kontrak':'h.OrderNo');
            })->join('ord_masterdoc as m','m.id','=',$contract?'a.master_doc_id':'a.MasterDocID')->whereIn('h.id',$ids)->selectRaw('h.id as header_id, m.DivisionID as department, '.($contract?'a.approver_status':'a.ApproverStatus').' as status, COUNT(*) as total')->groupBy('h.id','m.DivisionID',$contract?'a.approver_status':'a.ApproverStatus')->get();
            foreach($query as $row){$label=['P'=>'W','D'=>'R','C'=>'A'][$row->status]??null;if($label&&isset($stats[$kind.':'.$row->header_id][$row->department]))$stats[$kind.':'.$row->header_id][$row->department][$label]=(int)$row->total;}
        }
        return $stats;
    }
    public function find(array $companies,string $kind,int $id){return $this->query($companies)->where('kind',$kind)->where('id',$id)->firstOrFail();}
    public function header(string $kind,int $id,bool $lock=false){$q=$kind==='kontrak'?ContractInvoiceRegister::query():InvoiceRegister::query();if($lock)$q->lockForUpdate();return $q->findOrFail($id);}
    public function attachments($invoice){
        $contract=$invoice->kind==='kontrak';$q=$this->db()->table(($contract?'ordconfirmattachkontrak':'ordconfirmattach').' as a')->leftJoin('ord_masterdoc as m','m.id','=',$contract?'a.master_doc_id':'a.MasterDocID');
        $q->where($contract?'a.id_company':'a.CpnyID',$invoice->company)->where($contract?'a.ord_conf_id':'a.OrdConfID',$invoice->number)->where('a.vendor_id',$invoice->vendor_id)->where($contract?'a.id_kontrak':'a.OrderNo',$invoice->order_number);
        return $q->selectRaw($contract?'a.id, a.master_doc_id as master_id, a.filename, a.created_at as uploaded_at, a.approver_status as status, a.note, a.status_receive, m.document_name, m.DivisionID as department':'a.id, a.MasterDocID as master_id, a.Filename as filename, a.CreateDatetime as uploaded_at, a.ApproverStatus as status, a.Note as note, a.status_receive, m.document_name, m.DivisionID as department');
    }
    public function taxes($invoice){return $this->db()->table($invoice->kind==='kontrak'?'ordconfirmtaxinvkontrak':'ordconfirmtaxinv')->where('CpnyID',$invoice->company)->where('OrdConfID',$invoice->number)->where('VendorID',$invoice->vendor_id)->select('NoFaktur','tanggalFaktur','DPP','Tax','HargaTotal','ApproverStatus','ScanFakturPajak');}
    public function approvals($invoice){$contract=$invoice->kind==='kontrak';return $this->db()->table($contract?'approvaltransaksikontrak':'approvaltransaksi')->where($contract?'ord_conf_id':'OrdConfID',$invoice->number)->where($contract?'id_kontrak':'OrderNo',$invoice->order_number)->selectRaw($contract?'id, step_approval as step, dept_id as department, result, approved_by as actor, approved_at as date':'id, StepApproval as step, DeptID as department, result, ApprovedBy as actor, ApprovedAt as date');}
    public function history($invoice){$contract=$invoice->kind==='kontrak';return $this->db()->table(($contract?'ordconfirmattachkontrak_activity':'ordconfirmattach_activity').' as a')->leftJoin('admin as u','u.id','=','a.created_by')->where($contract?'a.ord_conf_id':'a.OrdConfID',$invoice->number)->where('a.vendor_id',$invoice->vendor_id)->orderByDesc('a.created_at')->orderByDesc('a.id')->get(['a.activity','a.notes','a.created_at','u.name','u.username']);}
    public function detail($invoice):array{
        $documents=$this->attachments($invoice)->orderBy('a.id')->get();foreach($documents as $document)$document->url=$this->fileUrl($invoice,$document->filename);
        $taxes=$this->taxes($invoice)->orderBy('NoFaktur')->get();$approvals=$this->approvals($invoice)->orderBy('id')->get();$history=$this->history($invoice);
        $receipts=$invoice->kind==='kontrak'?collect():$this->db()->table('ordconfirmreceipt')->where('id_ordconfirmhdr',$invoice->id)->orderBy('id')->get(['ReceiptNumber','ReceiptDate','TotalQty','Status']);
        $token=$this->token($invoice,$documents,$taxes,$approvals);
        return compact('invoice','documents','taxes','approvals','history','token','receipts');
    }
    public function token($invoice,$documents,$taxes,$approvals):string{return hash('sha256',json_encode([$invoice,$documents->map(fn($d)=>[$d->id,$d->status,$d->status_receive,$d->filename,$d->note]),$taxes,$approvals],JSON_THROW_ON_ERROR));}
    public function fileUrl($invoice,?string $filename):?string{
        $filename=trim((string)$filename);$order=(string)$invoice->order_number;
        if($filename===''||preg_match('/[\\\\\/\x00-\x1f]/',$filename)||str_contains($filename,'..')||!preg_match('/\A[A-Za-z0-9._-]+\z/D',$order))return null;
        return rtrim(config('vms.attachments_url'),'/').'/'.($invoice->kind==='kontrak'?'kontrak_attachment/':'ordattach/').rawurlencode($order).'/'.rawurlencode($filename);
    }
}
