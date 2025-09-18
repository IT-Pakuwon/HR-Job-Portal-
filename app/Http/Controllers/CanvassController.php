<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Autonbr;
use App\Models\T_Message;
use App\Models\Attachment;
use App\Models\M_approval;
use App\Models\M_approval_other;
use App\Models\T_approval;
use App\Models\Company;
use App\Models\Dept;
use App\Models\Usercpny;
use App\Models\Userdept;
use App\Models\User;
use App\Models\Site;
use App\Models\Division;
use App\Models\TrSPPB;
use App\Models\TrSPPBdetail;
use App\Models\TrSPPJ;
use App\Models\TrSPPJdetail;
use App\Models\TrSPPK;
use App\Models\TrSPPKdetail;
use App\Models\TrSPPT;
use App\Models\TrSPPTdetail;
use App\Models\MsLocationPG;
use App\Models\MsSubLocationPG;
use App\Models\vReceivedList;
use App\Models\vSppbjktOnProgress;
use App\Models\vCsJobs;
use App\Models\vCsRevision;
use Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;


class CanvassController extends Controller
{
    public function createCs(string $doc, string $src)
    {
        $doc = strtoupper($doc);
        abort_unless(in_array($doc, ['SPPB','SPPJ','SPPK','SPPT']), 404, 'Invalid doc type');

        $header = null;
        $detail = collect();
        $docno  = null;

        switch ($doc) {
            case 'SPPB':                
                $header = TrSPPB::with([
                    'requestType:requesttypeid,requesttype_name',
                    'creator:username,name',
                    'purchaser:username,name' 
                ])
                ->findOrFail($src); 
                $detail = TrSPPBdetail::where('sppbid', $header->sppbid)->get();
                $attachment = Attachment::where('docid', $header->sppbid)    
                    ->where('status','A')        
                    ->get();
                $docno  = $header->sppbno ?? $header->doc_no ?? $header->sppbid;
                break;

            case 'SPPJ':                
                $header = TrSPPJ::with([
                    'requestType:requesttypeid,requesttype_name',
                    'creator:username,name',
                    'purchaser:username,name' 
                ])
                ->findOrFail($src);
                $detail = TrSPPJdetail::where('sppjid', $header->sppjid)->get();
                $attachment = Attachment::where('docid', $header->sppjid)    
                    ->where('status','A')        
                    ->get();
                $docno  = $header->sppjno ?? $header->doc_no ?? $header->sppjid;
                break;

            case 'SPPK':
                $header = TrSPPK::with([
                    'requestType:requesttypeid,requesttype_name',
                    'creator:username,name',
                    'purchaser:username,name' 
                ])
                ->findOrFail($src);                
                $detail = TrSPPKdetail::where('sppkid', $header->sppkid)->get();
                $attachment = Attachment::where('docid', $header->sppkid)    
                    ->where('status','A')        
                    ->get();
                $docno  = $header->sppkno ?? $header->doc_no ?? $header->sppkid;
                break;

            case 'SPPT':                
                $header = TrSPPT::with([
                    'requestType:requesttypeid,requesttype_name',
                    'creator:username,name',
                    'purchaser:username,name' 
                ])
                ->findOrFail($src);
                $detail = TrSPPTdetail::where('spptid', $header->spptid)->get();
                $attachment = Attachment::where('docid', $header->spptid)    
                    ->where('status','A')        
                    ->get();
                $docno  = $header->spptno ?? $header->doc_no ?? $header->spptid;
                break;
        }
     
        $items = $detail;

        return view('pages.canvass.createcs', [
            'doc'     => $doc,
            'src_id'  => $src,
            'docno'   => $docno,
            'header'  => $header,
            'attachment'  => $attachment,
            'items'   => $items,  
        ]);
    }

    




}
