<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Autonbr;
use App\Models\User;
use App\Models\TrPO;
use App\Models\TrPOdetail;
use App\Models\T_approval;
use App\Models\Attachment;
use App\Models\T_Message;
use App\Models\MsVendor;
use App\Models\CompanyPG;
use App\Models\TrReceipt;
use App\Models\TrReceiptdetail;
use App\Models\TrCS;
use Vinkla\Hashids\Facades\Hashids;
use Mail;
use Barryvdh\DomPDF\Facade\Pdf; 


class ReceiptController extends Controller
{
    public function createReceipt(Request $req)
    {
        $ponbr_eid = (string) $req->query('ponbr', '');
        abort_if($ponbr_eid === '', 404, 'PO number required');

        $ponbr = Hashids::decode($ponbr_eid)[0] ?? null;
        abort_if(!$ponbr, 404);
        
        // --- ambil header PO ---
        $po = TrPO::select([
                'ponbr','podate','sppbjktid','vendorname','cpny_id',
                'department_id','user_peminta'
            ])->where('ponbr', $ponbr)->first();
            
        abort_if(!$po, 404, 'PO not found');

        // --- ambil detail PO ---
        $details = TrPOdetail::select([
                'id','ponbr',
                'inventoryid','inventory_descr',
                DB::raw("COALESCE(qty) as qty"),
                DB::raw("COALESCE(uom) as uom")
            ])
            ->where('ponbr', $ponbr)
            ->orderBy('id')
            ->get();

        // tampilan butuh list attachment existing? untuk create biasanya kosong
        $attachments = []; // biarkan kosong saat create

        return view('pages.receipt.createreceipt', [
            'po'          => $po,
            'details'     => $details,
            'attachments' => $attachments,
        ]);
    }

    public function storeReceipt(Request $request)
    {
        // dd($request->all());
        $user     = $request->user();
        $username = $user->username ?? 'system';

        $ponbr = trim((string)$request->input('ponbr', ''));
        if ($ponbr === '') {
            return back()->withErrors(['PO number not found.'])->withInput();
        }

        // ambil PO header
        $po = TrPo::where('ponbr', $ponbr)->first();
        if (!$po) {
            return back()->withErrors(['PO not found.'])->withInput();
        }

        // ambil detail PO
        $poDetails = TrPodetail::where('ponbr', $ponbr)
            ->select([
                'id','ponbr','inventoryid','inventory_descr','qty','uom','inventory_type'
            ])->get()->keyBy('id');

        // qty receipt dari form: qty_receipt[detail_id] => float
        $qtyReceiptInput = (array) $request->input('qty_receipt', []);
        $hasAnyQty = false;
        foreach ($qtyReceiptInput as $k => $v) {
            $qty = (float) str_replace(',', '.', (string)$v);
            if ($qty > 0) { $hasAnyQty = true; break; }
        }
        if (!$hasAnyQty) {
            return back()->withErrors(['Qty receipt minimal satu baris harus > 0.'])->withInput();
        }

        // === Approval line check (doctype GR) ===
        $doctype   = 'GR';
        $cpnyid    = $po->cpny_id ?? ($request->input('cpnyid') ?? null);
        $deptid    = $po->department_id ?? ($request->input('departementid') ?? null);
       
        
        DB::beginTransaction();
        try {
            // === Auto number: receiptnbr (GRYYMM####)
            $now   = Carbon::now();
            $year  = $now->year;
            $month = str_pad($now->month, 2, '0', STR_PAD_LEFT);

            $autonbr = Autonbr::lockForUpdate()
                ->where('doctype', $doctype)
                ->where('year', $year)
                ->where('month', $month)
                ->first();

            if (!$autonbr) {
                $autonbr = Autonbr::create([
                    'doctype' => $doctype,
                    'year'    => $year,
                    'month'   => $month,
                    'status'  => 'A',
                    'number'  => 1,
                ]);
                $urut = 1;
            } else {
                $urut = $autonbr->number + 1;
                $autonbr->update(['number' => $urut]);
            }

            $yymm        = substr($year, 2) . $month; // YYMM
            $receiptnbr  = $doctype . $yymm . sprintf('%04d', $urut);

            // === Header TrReceipt ===
            $header = new TrReceipt();
            $header->receiptnbr        = $receiptnbr;
            $header->receiptdate       = $now->toDateString();      // tanggal dokumen
            $header->receipttype       = $doctype;                  // 'GR'
            $header->ponbr             = $ponbr;
            $header->ref_receiptnbr    = null;
            $header->cpny_id           = $po->cpny_id ?? null;
            $header->csid              = $po->csid ?? null;
            $header->sppbjktid         = $po->sppbjktid ?? null;
            $header->department_id     = $po->department_id ?? null;
            $header->user_peminta      = $po->user_peminta ?? null;
            $header->receiptnote       = $request->input('receiptnote'); // kalau ada textarea note, opsional
            $header->vendorid          = $po->vendorid ?? null;
            $header->vendorname        = $po->vendorname ?? null;
            $header->totalqty_received = 0; // update setelah loop detail
            $header->status            = 'P';
            $header->created_by        = $username;
            $header->created_at        = $now;
            $header->save();

            // === Detail TrReceiptdetail ===
            $lineNo            = 0;
            $totalQtyReceived  = 0.0;

            foreach ($qtyReceiptInput as $detailId => $qtyRecRaw) {
                $qtyRec = (float) str_replace(',', '.', (string)$qtyRecRaw);
                if ($qtyRec <= 0) continue;

                $src = $poDetails->get((int)$detailId);
                if (!$src) continue; // skip bila id tidak cocok dgn PO detail

                $lineNo++;

                $det = new TrReceiptdetail();
                $det->receiptnbr                = $receiptnbr;
                $det->receipt_no                = $lineNo;
                $det->ponbr                     = $ponbr;
                $det->po_no                     = $src->ponbr; // jika memang ingin simpan lagi
                $det->csid                      = $po->csid ?? null;
                $det->cs_no                     = $po->cs_no ?? null;
                $det->sppbjktid                 = $po->sppbjktid ?? null;
                $det->sppbjktid_no              = $po->sppbjktid ?? null;

                $det->inventory_type            = $src->inventory_type ?? null;
                $det->inventoryid               = $src->inventoryid;
                $det->inventory_descr           = $src->inventory_descr;
                $det->qtyordered                = $src->qty;
                $det->uom                       = $src->uom;

                // base default = sama dgn qty/uom jika tak ada konversi
                $det->type_multiplier           = null;
                $det->base_multiplier           = 1;
                $det->base_qty                  = $qtyRec;
                $det->base_uom                  = $src->uom;

                $det->unitcost                  = null;
                $det->taxcodeid                 = null;
                $det->taxamt                    = null;
                $det->totalcost                 = null;

                $det->receipttype               = $doctype;

                // open ordered (sisa) bisa diisi jika kamu punya perhitungan; untuk sekarang isi null
                $det->qty_open_ordered          = null;
                $det->base_qty_open_ordered     = null;

                $det->qty_received              = $qtyRec;
                $det->base_qty_received         = $qtyRec;

                $det->qty_return                = 0;
                $det->base_qty_return           = 0;

                $det->ref_receiptnbr            = null;

                // budget fields (tak ada di form create receipt → kosongkan)
                $det->budget_perpost            = null;
                $det->budget_cpny_id            = null;
                $det->budget_business_unit_id   = null;
                $det->budget_department_fin_id  = null;
                $det->budget_account_id         = null;
                $det->budget_activity_id        = null;
                $det->budget_activity_descr     = null;

                $det->status                    = 'P';
                $det->created_by                = $username;
                $det->created_at                = $now;
                $det->save();

                $totalQtyReceived += $qtyRec;
            }

            if ($lineNo === 0) {
                // tidak ada detail valid (semua 0)
                DB::rollBack();
                return back()->withErrors(['Tidak ada Qty Receipt > 0 yang tersimpan.'])->withInput();
            }

            // update total qty header
            $header->totalqty_received = $totalQtyReceived;
            $header->save();
           

            // === Attachments (optional) ===
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    if (!$file) continue;

                    $randomNumber = random_int(10000000, 99999999);
                    $ext          = $file->getClientOriginalExtension();
                    $filename     = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $attachfile   = md5($randomNumber) . '.' . $ext;

                    $folder = public_path().'/attachments/'.$year;
                    if (!is_dir($folder)) { @mkdir($folder, 0777, true); }

                    $file->move($folder, $attachfile);

                    Attachment::create([
                        'docid'      => $receiptnbr,
                        'name'       => $filename,
                        'attachfile' => $attachfile,
                        'status'     => 'A',
                        'extention'  => $ext,
                        'created_user' => $username,
                    ]);
                }
            }

            // === Kirim email ke approver pertama ===
            // $firstApproval = T_approval::where('docid', $receiptnbr)
            //     ->where('status', 'P')
            //     ->orderBy('aprvid')->first();

            // if ($firstApproval) {
            //     $status = $header->status; // 'P' default

            //     $subjectMap = [
            //         'P' => 'Waiting Approval',
            //         'R' => 'Rejected',
            //         'D' => 'Revise',
            //         'A' => 'Approved',
            //         'C' => 'Completed',
            //     ];
            //     $subjectSuffix = $subjectMap[$status] ?? 'Notification';

            //     $eid = Hashids::encode($header->id);

            //     $data = [
            //         'docid'     => $receiptnbr,
            //         'cpnyid'    => $cpnyid,
            //         'deptname'  => $deptid,
            //         'date'      => $now->toDateTimeString(),
            //         'name'      => $firstApproval->name,
            //         'createdby' => $header->created_by,
            //         'info'      => $header->receiptnote,
            //         'status'    => $status,
            //         'docname'   => 'GR',
            //         'url'       => url('/showreceipts/'.$eid), // ⬅️ sesuaikan dgn rute show receipt kamu
            //     ];

            //     $approvers = array_filter(array_map('trim', explode(',', (string)$firstApproval->aprvusername)));
            //     $emails = User::whereIn('username', $approvers)
            //         ->where('status', 'A')
            //         ->pluck('test_email');

            //     foreach ($emails as $email) {
            //         \Mail::send('emails.mailapprovenew', $data, function ($message) use ($email, $data, $subjectSuffix) {
            //             $message->to($email)
            //                 ->subject($data['docid'].' - '.$subjectSuffix.' GR')
            //                 ->from('digitalserver@pakuwon.com', 'Pakuwon System');
            //         });
            //     }
            // }

            DB::commit();

            return redirect()
                ->route('receiptlist.index')
                ->with('success', "Receipt {$receiptnbr} created. Total Qty: {$totalQtyReceived}");

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withErrors([config('app.debug') ? $e->getMessage() : 'Failed to create Receipt'])->withInput();
        }
    }

    public function fetchComments($id)
    {
    
        $comments = T_Message::where('docid', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'comments' => $comments
        ]);
    }

    public function storeComment(Request $request, $id)
    {
        $user = Auth::user();
        $request->validate([
            'comment' => 'required|string|max:500',
        ]);
        // dd($id);
        $user = request()->user();
        $comment = new T_Message();
        $comment->docid = $id;
        $comment->doctype = 'PO';
        $comment->username = $user->username; 
        $comment->name = $user->name; 
        $comment->message = $request->comment;
        $comment->status = 'A';
        $comment->created_at = now();
        $comment->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Comment added successfully!',
            'comment' => $comment
        ]);
    }

    public function uploadAttachments(Request $request, $poid)
    {
        try {
            $user = $request->user();
            $year = (int) ($request->input('year') ?? now()->year);

            $created = [];

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $randomNumber = random_int(10000000, 99999999);
                    $filename     = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

                    // bersihkan nama original dari %
                    $originalName = str_replace('%', '', $file->getClientOriginalName());
                    $ext        = $file->getClientOriginalExtension();
                    $attachfile = md5($randomNumber) . '.' . $ext;

                    // folder tujuan
                    $folder_attach = public_path('attachments/'.$year);
                    if (!is_dir($folder_attach)) {
                        @mkdir($folder_attach, 0777, true);
                    }

                    // pindahkan file (tanpa ekstensi di nama file, sesuai contoh kamu)
                    $file->move($folder_attach, $attachfile);

                    // simpan DB
                    $attach = new Attachment();
                    $attach->docid       = $poid;
                    $attach->name        = $filename; // tampilkan nama tanpa ekstensi
                    $attach->attachfile  = $attachfile;
                    $attach->status      = 'A';
                    $attach->extention   = $file->getClientOriginalExtension();
                    $attach->created_user= $user->username ?? 'system';
                    $attach->save();

                    $created[] = [
                        'id'         => $attach->id,
                        'name'       => $attach->name,
                        'attachfile' => $attach->attachfile,
                        'ext'        => $attach->extention,
                        'year'       => $year,
                    ];
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No files received.'
                ], 422);
            }

            return response()->json([
                'success'     => true,
                'attachments' => $created
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
   
    public function listAttachment($ponbr)
    {
        $rows = Attachment::where('docid', $ponbr)
            ->where('status', 'A')
            ->orderByDesc('id')->get()
            ->map(function($a){
            return [
                'id'         => $a->id,
                'name'       => $a->name . '.' . $a->extention,
                'attachfile' => $a->attachfile,               // sudah termasuk extension
                'year'       => optional($a->created_at)->year ?? now()->year,
                'created_at' => optional($a->created_at)->toDateTimeString(),
                'created_user'=> $a->created_user,
                'url'        => url('/attachments/'.(optional($a->created_at)->year ?? now()->year).'/'.$a->attachfile),
            ];
        });

        return response()->json(['success'=>true, 'attachments'=>$rows]);
    }
    

    public function removeAttachment($id)
    {
        try {
            $attachment = Attachment::findOrFail($id);
            $attachment->update(['status' => 'X']); // Update status ke "D" (Deleted)

            return response()->json(['success' => true, 'message' => 'Attachment status updated']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update attachment status', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['success'=>true]);
    }




    


}
