<?php
namespace App\Services\Vms;
use Illuminate\Validation\ValidationException;

class InvoiceRegisterWorkflow {
    private function allowed(array $actor,string $department):void {abort_unless($actor['id']&&($actor['all']||in_array($department,$actor['departments'],true)),403,'Akun Anda belum memiliki kewenangan VMS untuk tindakan ini.');}
    public function decide(InvoiceRegisterDirectory $directory,array $companies,string $kind,int $id,array $actor,array $data):void {
        $delivery=$directory->db()->transaction(function()use($directory,$companies,$kind,$id,$actor,$data){
            $invoice=$directory->find($companies,$kind,$id);$header=$directory->header($kind,$id,true);$invoice=$directory->find($companies,$kind,$id);
            $directory->attachments($invoice)->lockForUpdate()->get();$directory->taxes($invoice)->lockForUpdate()->get();$directory->approvals($invoice)->lockForUpdate()->get();
            $detail=$directory->detail($invoice);abort_unless(hash_equals($detail['token'],$data['token']),409,'Data sudah berubah. Tutup dan buka kembali detail invoice.');
            $contract=$kind==='kontrak';$action=$data['action'];$note=trim((string)($data['note']??''));$now=now();$db=$directory->db();
            if(in_array($action,['revise','reject','return'],true)&&$note==='')throw ValidationException::withMessages(['note'=>'Catatan wajib diisi.']);
            $statusField=$contract?'status':'Status';$sendField=$contract?'proforma_status_approval':'ProformaStatusApproval';
            $approvalTable=$contract?'approvaltransaksikontrak':'approvaltransaksi';$conf=$contract?'ord_conf_id':'OrdConfID';$order=$contract?'id_kontrak':'OrderNo';$step=$contract?'step_approval':'StepApproval';$dept=$contract?'dept_id':'DeptID';$by=$contract?'approved_by':'ApprovedBy';$at=$contract?'approved_at':'ApprovedAt';$module=$contract?'module':'Module';
            $approvalKey=[$conf=>$invoice->number,$order=>$invoice->order_number];
            $documentName=null;
            if($contract&&!in_array($action,['receive','return'],true))abort_if($db->table('ordconfirmkontrak')->where('id_ordconfirmhdrkontrak',$id)->where('status','!=','C')->exists(),409,'Persetujuan user Kontrak belum selesai.');
            if(in_array($action,['receive','return'],true)){
                $this->allowed($actor,'finance');abort_unless($invoice->status==='C',409,'Dokumen fisik hanya dapat diproses setelah invoice completed.');
                $send=$action==='receive'?'approve':'return';abort_if($invoice->send_status===$send,409,'Status dokumen fisik sudah sama.');
                $header->setAttribute($sendField,$send);$header->setAttribute($contract?'proforma_approver':'ProformaApprover',$actor['id']);$header->setAttribute($contract?'proforma_approve_date':'ProformaApproveDate',$now);$header->setAttribute('note',$action==='return'?$note:null);$header->save();
                $directory->attachments($invoice)->update(['status_receive'=>$send]);$activity=$action==='receive'?'Receive Invoice Confirmation':'Return All Document';
            }elseif($action==='final'){
                $this->allowed($actor,'finance');abort_unless($invoice->status==='A',409,'Invoice belum siap untuk final approval Finance.');
                abort_if($detail['documents']->isEmpty()||$detail['documents']->contains(fn($d)=>$d->status!=='C')||$detail['taxes']->contains(fn($t)=>$t->ApproverStatus!=='C'),409,'Seluruh dokumen dan faktur pajak harus disetujui terlebih dahulu.');
                $header->setAttribute($statusField,'C');if($invoice->send_status==='revise')$header->setAttribute($sendField,'not_send');$header->save();
                $db->table($approvalTable)->updateOrInsert($approvalKey+[$step=>2,$dept=>'finance'],['result'=>'C',$by=>$actor['id'],$at=>$now,$module=>$invoice->module,'ICDP'=>'IC','created_at'=>$now]);
                if(!$contract)$db->table('ordconfirmreceipt')->where('id_ordconfirmhdr',$id)->update(['Status'=>'C']);
                $activity='Approve Final by Finance';
            }else{
                abort_unless(in_array($invoice->status,['P','A'],true),409,'Invoice tidak sedang menunggu review.');
                $taxNumber=$data['tax_invoice']??null;
                if($taxNumber){
                    $this->allowed($actor,'tax');$tax=$detail['taxes']->firstWhere('NoFaktur',$taxNumber);abort_unless($tax,404);abort_unless($action!=='approve'||$tax->ApproverStatus!=='C',409,'Faktur sudah disetujui.');$department='tax';$documentName='Faktur Pajak '.$taxNumber;
                    $directory->taxes($invoice)->where('NoFaktur',$taxNumber)->update(['ApproverStatus'=>['approve'=>'C','revise'=>'D','reject'=>'R'][$action],'ApproverUser'=>$actor['id'],'Approved_at'=>$now,$contract?'Notes':'Note'=>$note?:null]);
                }else{
                    $document=$detail['documents']->firstWhere('id',(int)($data['document']??0));abort_unless($document,404);$department=strtolower((string)$document->department);$this->allowed($actor,$department);abort_unless($action!=='approve'||$document->status!=='C',409,'Dokumen sudah disetujui.');abort_if($action==='approve'&&!$document->url,422,'Dokumen belum memiliki file.');$documentName=$document->document_name;
                    $directory->attachments($invoice)->where('a.id',$document->id)->update([$contract?'approver_status':'ApproverStatus'=>['approve'=>'C','revise'=>'D','reject'=>'R'][$action],$contract?'approver_user':'ApproverUser'=>$actor['id'],$contract?'approved_at':'Approved_at'=>$now,$contract?'note':'Note'=>$note?:null]);
                }
                $updated=$directory->detail($invoice);$departmentComplete=$updated['documents']->where('department',$department)->every(fn($d)=>$d->status==='C')&&($department!=='tax'||$updated['taxes']->every(fn($t)=>$t->ApproverStatus==='C'));
                $db->table($approvalTable)->updateOrInsert($approvalKey+[$step=>1,$dept=>$department],['result'=>$action==='approve'?($departmentComplete?'C':'P'):($action==='revise'?'D':'R'),$by=>$actor['id'],$at=>$now,$module=>$invoice->module,'ICDP'=>'IC','created_at'=>$now]);
                if($action==='approve'){
                    if($updated['documents']->isNotEmpty()&&$updated['documents']->every(fn($d)=>$d->status==='C')&&$updated['taxes']->every(fn($t)=>$t->ApproverStatus==='C')){
                        $header->setAttribute($statusField,'A');$header->setAttribute($contract?'approver_status':'ApproveStatus','A');$db->table($approvalTable)->updateOrInsert($approvalKey+[$step=>2,$dept=>'finance'],['result'=>'A',$module=>$invoice->module,'ICDP'=>'IC','created_at'=>$now]);
                    }
                }else{
                    $header->setAttribute($statusField,$action==='revise'?'D':'R');$header->setAttribute('note',$note);$db->table($approvalTable)->where($approvalKey)->where($step,2)->update(['result'=>$action==='revise'?'D':'R']);
                    if($contract)$db->table('ordconfirmkontrak')->where('id_ordconfirmhdrkontrak',$id)->update(['status'=>$action==='revise'?'D':'R']);
                }
                $header->save();$activity=['approve'=>'Approve','revise'=>'Revise','reject'=>'Reject'][$action].' '.$documentName;
            }
            if(!in_array($action,['receive','return'],true))$db->table($contract?'approvaltransaksikontrak_history':'approvaltransaksi_history')->insert($approvalKey+[$step=>$action==='final'?2:1,$dept=>$action==='final'?'finance':$department,'result'=>['approve'=>'C','revise'=>'D','reject'=>'R','final'=>'C'][$action],$by=>$actor['id'],$at=>$now,$module=>$invoice->module,'ICDP'=>'IC','created_at'=>$now]);
            $activityId=$db->table($contract?'ordconfirmattachkontrak_activity':'ordconfirmattach_activity')->insertGetId([$conf=>$invoice->number,'sec_vendor_id'=>$invoice->second_vendor_id,'vendor_id'=>$invoice->vendor_id,'activity'=>$activity,'notes'=>$note?:null,'created_by'=>$actor['id'],'created_at'=>$now]);
            if($action==='receive')return null;
            $email=app(RegistrationEmail::class);$recipients=$db->table('vendor')->where('vendor_id',$invoice->vendor_id)->first(['email','email2']);
            return $email->enqueue($action==='return'?'invoice_returned':'invoice_document_reviewed','internal-ir:'.$kind.':'.$id.':activity:'.$activityId,[$recipients?->email,$recipients?->email2],['vendor'=>$invoice->vendor_name,'invoice'=>$invoice->number,'order'=>$invoice->order_number,'document'=>$documentName,'status'=>$activity,'notes'=>$note]);
        });
        app(RegistrationEmail::class)->deliver($delivery);
    }
}
