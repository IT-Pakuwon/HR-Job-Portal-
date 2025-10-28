<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Autonbr;
use App\Models\User;
use App\Models\T_approval;
use App\Models\Attachment;
use App\Models\T_Message;
use App\Models\MsVendor;
use App\Models\CompanyPG;
use App\Models\TrIssue;
use App\Models\TrIssuedetail;
use App\Models\TrSPB;
use App\Models\TrSPBdetail;
use Vinkla\Hashids\Facades\Hashids;
use Mail;
use Barryvdh\DomPDF\Facade\Pdf; 
use App\Models\Company;
use App\Http\Controllers\TrAttachmentController;


class IssueController extends Controller
{
    public function createIssue(Request $req) 
    {
        // Ambil spbid (plain) dari query
        $spbid = (string) $req->query('spbid', '');
        abort_if($spbid === '', 404, 'SPB ID required');

        // --- Ambil header SPB ---
        $spb = TrSPB::select([
                'id','spbid','spbdate','cpny_id','department_id','keperluan'
            ])
            ->where('spbid', $spbid)
            ->first();

        abort_if(!$spb, 404, 'SPB not found');

        // --- Ambil detail SPB + qty sisa untuk ISSUE ---
        // Gunakan spb_openqty sebagai sisa yang bisa di-issue
        $details = TrSPBdetail::select([
                'id','spbid','spb_no',
                'inventoryid','inventory_descr','siteid',
                DB::raw("COALESCE(uom,'') AS uom"),
                DB::raw("COALESCE(qty,0) AS qty_original"),
                DB::raw("COALESCE(spb_openqty,0) AS qty_sisa")
            ])
            ->where('spbid', $spb->spbid)
            ->orderBy('id')
            ->get()
            ->filter(fn($r) => (float)$r->qty_sisa > 0)
            ->map(function ($r) {
                // supaya view tetap pakai $d->qty → set ke qty_sisa
                $r->qty = (float) $r->qty_sisa;
                return $r;
            })
            ->values();

        $attachments = []; // biasanya kosong saat create

        return view('pages.issue.createissue', [
            'spb'         => $spb,
            'details'     => $details,
            'attachments' => $attachments,
        ]);
    }
    
    public function storeIssue(Request $request)
    {
        $user     = $request->user();
        $username = $user->username ?? 'system';

        $spbid = trim((string)$request->input('spbid', ''));
        if ($spbid === '') {
            return back()->withErrors(['SPB ID tidak ditemukan.'])->withInput();
        }

        // ===== Ambil header SPB =====
        $spb = TrSPB::where('spbid', $spbid)->first();
        if (!$spb) {
            return back()->withErrors(['SPB tidak ditemukan.'])->withInput();
        }

        // ===== Ambil detail SPB (keyBy id untuk akses cepat) =====
        $spbDetails = TrSPBdetail::where('spbid', $spbid)->get()->keyBy('id');

        // ===== Ambil input qty_issue & siteid =====
        $qtyIssueInput = (array) $request->input('qty_issue', []);
        $siteInput     = (array) $request->input('siteid', []);

        // Minimal satu baris > 0
        $hasAnyQty = false;
        foreach ($qtyIssueInput as $k => $v) {
            $qty = (float) str_replace(',', '.', (string)$v);
            if ($qty > 0) { $hasAnyQty = true; break; }
        }
        if (!$hasAnyQty) {
            return back()->withErrors(['Qty Issue minimal satu baris harus > 0.'])->withInput();
        }

        // Validasi per-baris: qty_issue <= spb_openqty
        foreach ($qtyIssueInput as $detailId => $v) {
            $qty = (float) str_replace(',', '.', (string)$v);
            if ($qty <= 0) continue;
            $src = $spbDetails->get((int)$detailId);
            if (!$src) {
                return back()->withErrors(["Detail SPB (ID: {$detailId}) tidak ditemukan."])->withInput();
            }
            $open = (float) ($src->spb_openqty ?? 0);
            if ($qty > $open) {
                return back()->withErrors(["Qty Issue untuk item {$src->inventoryid} melebihi sisa open ({$open})."])->withInput();
            }
        }

        // ===== Siapkan info untuk autonumber & header =====
        $doctype = 'IS';
        $now     = Carbon::now();
        $year    = $now->year;
        $month   = str_pad($now->month, 2, '0', STR_PAD_LEFT);

        \DB::beginTransaction();
        try {
            // ===== Ambil / tingkatkan autonumber untuk doctype IS (YYMM####) =====
            /** @var Autonbr $autonbr */
            $autonbr = Autonbr::lockForUpdate()
                ->where('doctype', $doctype)
                ->where('year', $year)
                ->where('month', $month)
                ->first();

            if (!$autonbr) {
                $autonbr = Autonbr::create([
                    'doctype' => $doctype,
                    'year'    => (int) $year,
                    'month'   => (int) $month,
                    'status'  => 'A',
                    'number'  => 1,
                ]);
                $urut = 1;
            } else {
                $urut = (int) $autonbr->number + 1;
                $autonbr->update(['number' => $urut]);
            }

            $yymm    = substr((string)$year, 2) . $month;         // YYMM
            $issueid = $doctype . $yymm . sprintf('%04d', $urut); // contoh: IS2510xxxx

            // ===== Simpan header TrIssue =====
            $header = new TrIssue();
            $header->issueid        = $issueid;
            $header->issuedate      = $now->toDateString();
            $header->issuetype      = 'IS';
            $header->spbid          = $spbid;
            $header->cpny_id        = $spb->cpny_id ?? null;
            $header->department_id  = $spb->department_id ?? null;
            $header->user_peminta   = $spb->created_by ?? null; // atau field lain jika ada
            $header->budget_perpost = $spb->budget_perpost ?? null;
            $header->issuenote      = (string) $request->input('issuenote', '');
            $header->totalissueqty  = 0;   // diupdate setelah loop detail
            $header->status         = 'P';
            $header->created_by     = $username;
            $header->created_at     = $now;
            $header->save();

            // ===== Loop detail input → simpan TrIssuedetail + update SPB detail =====
            $lineNo          = 0;
            $totalIssueQty   = 0.0;

            foreach ($spbDetails as $detailId => $src) {
                $qtyRecRaw = $qtyIssueInput[$detailId] ?? 0;
                $qtyIssue  = (float) str_replace(',', '.', (string)$qtyRecRaw);
                if ($qtyIssue <= 0) continue;

                $siteFromForm = isset($siteInput[$detailId]) ? trim((string)$siteInput[$detailId]) : null;

                $lineNo++;

                $det = new TrIssuedetail();
                $det->issueid               = $issueid;
                $det->issue_no              = $lineNo;

                // relasi SPB
                $det->spbid                 = $spbid;
                $det->spb_no                = $src->spb_no ?? null;

                // inventory
                $det->issuetype             = 'IS';
                $det->inventoryid           = $src->inventoryid;
                $det->inventory_descr       = $src->inventory_descr;
                $det->siteid                = $siteFromForm !== '' ? $siteFromForm : ($src->siteid ?? null);

                // Kuantitas untuk dokumen issue
                // Catatan: di model detail ada 'qty' dan 'issue_qty'. Kita isi keduanya dengan qty issue line ini.
                $det->qty                   = $qtyIssue;
                $det->uom                   = $src->uom ?? null;

                // Base fields (tanpa konversi)
                $det->type_multiplier       = 1;
                $det->base_multiplier       = 1;
                $det->base_qty              = $qtyIssue;
                $det->base_uom              = $src->uom ?? null;

                // Harga (tidak ada di TrSPBdetail, set 0/null)
                $det->unitcost              = 0;
                $det->totalcost             = 0;

                $det->note                  = null;

                // Budget (ikuti field yang ada di TrSPBdetail jika tersedia)
                $det->budget_perpost              = $src->budget_perpost ?? null;
                $det->budget_cpny_id              = $src->budget_cpny_id ?? null;
                $det->budget_business_unit_id     = $src->budget_business_unit_id ?? null;
                $det->budget_department_fin_id    = $src->budget_department_fin_id ?? null;
                $det->budget_account_id           = $src->budget_account_id ?? null;
                $det->budget_activity_id          = $src->budget_activity_id ?? null;

                // Issue qty (kolom di model detail)
                $det->issue_qty             = $qtyIssue;

                $det->status                = 'P';
                $det->created_by            = $username;
                $det->created_at            = $now;

                $det->save();

                // ==== Update SPB detail (open turun, issued naik) ====
                $src->spb_openqty = (float) ($src->spb_openqty ?? 0) - $qtyIssue;
                if ($src->spb_openqty < 0) $src->spb_openqty = 0;
                $src->issue_qty   = (float) ($src->issue_qty ?? 0) + $qtyIssue;
                $src->updated_by  = $username;
                $src->updated_at  = $now;
                $src->save();

                $totalIssueQty += $qtyIssue;
            }

            if ($totalIssueQty <= 0) {
                \DB::rollBack();
                return back()->withErrors(['Qty Issue minimal satu baris harus > 0.'])->withInput();
            }

            // ===== Update total di header Issue =====
            $header->totalissueqty = $totalIssueQty;
            $header->save();

            // (Opsional) Rekalkulasi total di header SPB
            // $reAgg = TrSPBdetail::where('spbid', $spbid)
            //     ->selectRaw('
            //         COALESCE(SUM(issue_qty),0)      AS total_issue,
            //         COALESCE(SUM(spb_openqty),0)    AS total_open,
            //         COALESCE(SUM(spb_completeqty),0) AS total_complete
            //     ')->first();

            // if ($reAgg) {
            //     $spb->totalissueqty     = (float) $reAgg->total_issue;
            //     $spb->totalspbopenqty   = (float) $reAgg->total_open;
            //     $spb->totalcompleteqty  = (float) $reAgg->total_complete;
            //     $spb->updated_by        = $username;
            //     $spb->updated_at        = $now;
            //     $spb->save();
            // }

            // ===== Attachments (opsional) =====
            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr'        => $issueid,
                    'doctype'       => $doctype,
                    'cpnyid'        => $spb->cpny_id ?? null,
                    'departementid' => $spb->department_id ?? null,
                    'base_folder'   => 'att-issue/'.strtolower($doctype),
                    'created_by'    => $username,
                ];

                $files = (array) $request->file('attachments');

                try {
                    $uploader = app(TrAttachmentController::class);
                    $uploader->uploadInternal($meta, $files);
                } catch (\Throwable $e) {
                    \DB::rollBack();
                    return back()->withErrors(['Gagal upload attachment: '.$e->getMessage()])->withInput();
                }
            }

            \DB::commit();

            return redirect()
                ->route('issuelist.index')
                ->with('success', "Issue {$issueid} created. Total Qty: {$totalIssueQty}");
        } catch (\Throwable $e) {
            \DB::rollBack();
            return back()->withErrors([config('app.debug') ? $e->getMessage() : 'Failed to create Issue'])->withInput();
        }
    }

    public function showIssue($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        // ===== Header Issue (pakai issueid, sesuai model)
        /** @var TrIssue $iss */
        $iss = TrIssue::findOrFail($id);

        // ===== Detail Issue (berdasarkan issueid)
        $issdetail = TrIssuedetail::where('issueid', $iss->issueid)
            ->orderBy('issue_no')
            ->get();

        // ===== Attachment by issueid (doctype IS)
        $attachment = Attachment::where('docid', $iss->issueid)
            ->where('status', 'A')
            ->get();

        // ===== Link ke SPB (opsional) -> /showspbs/{hash}
        $spbUrl = null;
        if (!empty($iss->spbid)) {
            $spbId = TrSPB::where('spbid', $iss->spbid)->value('id');
            if ($spbId) {
                $spbHash = Hashids::encode($spbId);
                $spbUrl  = url("/showspbs/{$spbHash}");
            }
        }

        // convenience id terenkripsi untuk share (kalau perlu)
        $eid_issueid = Hashids::encode($iss->id);

        return view('pages.issue.showissue', [
            'iss'         => $iss,
            'issdetail'   => $issdetail,
            'attachment'  => $attachment,
            'hash'        => $hash,
            'eid_issueid' => $eid_issueid,
            'spbUrl'      => $spbUrl,
        ]);
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
        $comment->doctype = 'SPB';
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

    public function uploadAttachments(Request $request, $spbid)
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
                    $attach->docid       = $spbid;
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
   
    public function listAttachment($spbid)
    {
        $rows = Attachment::where('docid', $spbid)
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

    
    public function printIssue(string $hash, Request $request)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = auth()->user();
        if (!$user) return redirect()->route('login');

        $iss = TrIssue::with(['creator:username,name'])->findOrFail($id);
        $spb  = TrSPB::where('spbid', $iss->spbid)->first();
        $issdetails = TrIssuedetail::where('issueid', $iss->issueid)
            ->orderBy('issue_no')->get();
        $company = Company::where('cpnyid', $iss->cpny_id)->first();

        $data = compact('iss','spb','issdetails','company');

        $type = strtolower((string)$request->query('type', 'sttb'));
        $view = $type === 'bpg' ? 'pages.issue.pdf_bpg' : 'pages.issue.pdf_issue';

        $createdName = ucwords(strtolower(optional($iss->creator)->name ?? $iss->created_by));
        $now = now();

        $pdf = Pdf::loadView($view, $data)->setPaper('A4','spbrtrait');

        $dompdf = $pdf->getDomPDF();
        $dompdf->render();

        // footer
        $canvas  = $dompdf->get_canvas();
        $w       = $canvas->get_width();
        $h       = $canvas->get_height();
        $metrics = $dompdf->getFontMetrics();
        $font    = $metrics->get_font('sans-serif', 'normal');
        $size    = 9;

        $leftTxt  = "Created by: {$createdName}, Sent by: {$createdName}, On: ".$now->format('d/m/Y H:i');
        $rightTpl = "Page {PAGE_NUM} of {PAGE_COUNT}";
        $rightWidth = $metrics->getTextWidth($rightTpl, $font, $size);
        $y = $h - 28;
        $x = $canvas->get_width() - $w - 75;
        $canvas->page_text(30, $y, $leftTxt, $font, $size, [0,0,0]);
        $canvas->page_text($w - $x - $rightWidth, $y, $rightTpl, $font, $size, [0,0,0]);

        $basename = $type === 'bpg' ? 'BPG' : 'STTB';
        return $dompdf->stream("{$basename}_{$iss->issueid}.pdf", ['Attachment' => false]);
    }

    public function approveIssue(Request $request, $id)
    {
        return \DB::connection('pgsql')->transaction(function () use ($id) {

            $user  = \Auth::user();
            $uname = $user->username ?? null;
            $now   = Carbon::now();

            // ===== Lock header Issue          
            $iss = TrIssue::where('id', $id)->lockForUpdate()->firstOrFail();

            if ($iss->status !== 'P') {
                return response()->json([
                    'ok'      => false,
                    'message' => 'Issue tidak dalam status PENDING.'
                ], 422);
            }

            // ===== Ambil semua detail Issue            
            $issdetails = TrIssuedetail::where('issueid', $iss->issueid)
                ->orderBy('issue_no')
                ->get();

            if ($issdetails->isEmpty()) {
                return response()->json([
                    'ok'      => false,
                    'message' => 'Tidak ada detail issue untuk diproses.'
                ], 422);
            }

            // ===== Lock SPB terkait
            /** @var TrSPB|null $spb */
            $spb = TrSPB::where('spbid', $iss->spbid)->lockForUpdate()->first();
            if (!$spb) {
                return response()->json([
                    'ok'      => false,
                    'message' => 'SPB terkait tidak ditemukan.'
                ], 422);
            }

            // ===== Ambil semua detail SPB untuk pemetaan
            /** @var \Illuminate\Support\Collection|TrSPBdetail[] $spbDetailRows */
            $spbDetailRows = TrSPBdetail::where('spbid', $iss->spbid)->get();

            // Kunci pencocokan utama: inventoryid|uom|spb_no; fallback: inventoryid|uom
            $spbByKeyFull = $spbDetailRows->keyBy(function ($r) {
                return ($r->inventoryid ?? '') . '|' . ($r->uom ?? '') . '|' . ($r->spb_no ?? '');
            });
            $spbByKey = $spbDetailRows->keyBy(function ($r) {
                return ($r->inventoryid ?? '') . '|' . ($r->uom ?? '');
            });

            $sumIssueThisDoc  = 0.0; // total issue pada dokumen ini (untuk issuetype='issue')
            $sumReturnThisDoc = 0.0; // total return pada dokumen ini (untuk issuetype='return')

            // ===== Helper untuk clamp angka 0..qty
            $clamp = function (float $val, float $min, float $max) {
                return max($min, min($max, $val));
            };

            if ($iss->issuetype === 'IS') {
                // ========== APPROVE ISSUE ==========
                foreach ($issdetails as $rd) {
                    $qty = (float) ($rd->issue_qty ?? 0);
                    if ($qty <= 0) continue;

                    $keyFull = ($rd->inventoryid ?? '') . '|' . ($rd->uom ?? '') . '|' . ($rd->spb_no ?? '');
                    $keyBase = ($rd->inventoryid ?? '') . '|' . ($rd->uom ?? '');

                    /** @var TrSPBdetail|null $spbDet */
                    $spbDet = $spbByKeyFull->get($keyFull) ?? $spbByKey->get($keyBase);
                    if (!$spbDet) continue;

                    $ordered      = (float) ($spbDet->qty ?? 0);
                    $issuedSoFar  = (float) ($spbDet->issue_qty ?? 0);
                    $openSoFar    = (float) ($spbDet->spb_openqty ?? max($ordered - $issuedSoFar, 0));

                    // Tambah issue, kurangi open
                    $newIssued = $this->clamp(($issuedSoFar + $qty), 0.0, $ordered);
                    $delta     = $newIssued - $issuedSoFar; // real yang ditambahkan (terclamp)
                    $newOpen   = $this->clamp(($openSoFar - $delta), 0.0, $ordered);

                    $spbDet->issue_qty       = $newIssued;
                    $spbDet->spb_openqty     = $newOpen;
                    $spbDet->spb_completeqty = $this->clamp(($spbDet->spb_completeqty ?? 0) + $delta, 0.0, $ordered);

                    // Status detail
                    if ($newOpen <= 0.0000001) {
                        $spbDet->status = 'C';
                    } else {
                        $spbDet->status = 'P';
                    }

                    $spbDet->updated_by = $uname;
                    $spbDet->save();

                    $sumIssueThisDoc += $delta;
                }

                // ===== Recalculate header SPB totals (dari tabel detail)
                $totals = TrSPBdetail::selectRaw("
                        COALESCE(SUM(qty),0)              AS total_spbqty,
                        COALESCE(SUM(spb_openqty),0)      AS total_spbopenqty,
                        COALESCE(SUM(issue_qty),0)        AS total_issueqty,
                        COALESCE(SUM(spb_completeqty),0)  AS total_completeqty
                    ")
                    ->where('spbid', $iss->spbid)
                    ->first();

                $spb->totalspbqty       = (float) $totals->total_spbqty;
                $spb->totalspbopenqty   = (float) $totals->total_spbopenqty;
                $spb->totalissueqty     = (float) $totals->total_issueqty;
                $spb->totalcompleteqty  = (float) $totals->total_completeqty;

                // Close SPB jika semua open = 0
                if ($spb->totalspbopenqty <= 0.0000001) {
                    $spb->status       = 'C';
                    $spb->completed_by = $uname;
                    // jika punya kolom completed_at di DB, set di sini; jika tidak, hapus baris ini
                    // $spb->completed_at = $now;
                } else {
                    // tetap progress
                    $spb->status = 'P';
                }
                $spb->updated_by = $uname;
                $spb->save();

                // ===== Update header Issue
                $iss->status           = 'C';
                $iss->totalissueqty    = (float) (($iss->totalissueqty ?? 0) + $sumIssueThisDoc);
                $iss->completed_by     = $uname;
                // jika ada kolom completed_at, aktifkan:
                // $iss->completed_at     = $now;
                $iss->updated_by       = $uname;
                $iss->save();

                return response()->json([
                    'ok'      => true,
                    'message' => 'Issue berhasil di-approve & SPB diperbarui.',
                    'data'    => [
                        'type'                => $iss->issuetype,
                        'issueid'             => $iss->issueid,
                        'spbid'               => $iss->spbid,
                        'added_issue_qty'     => $sumIssueThisDoc,
                        'spb_total_issueqty'  => (float) $spb->totalissueqty,
                        'spb_total_openqty'   => (float) $spb->totalspbopenqty,
                        'spb_status'          => $spb->status,
                    ],
                ]);
            }

            if ($iss->issuetype === 'RI') {
                // ========== APPROVE RETURN ==========
                foreach ($issdetails as $rd) {
                    $qty = (float) ($rd->issue_qty ?? 0); // dipakai sebagai qty return dari dokumen return
                    if ($qty <= 0) continue;

                    $keyFull = ($rd->inventoryid ?? '') . '|' . ($rd->uom ?? '') . '|' . ($rd->spb_no ?? '');
                    $keyBase = ($rd->inventoryid ?? '') . '|' . ($rd->uom ?? '');

                    /** @var TrSPBdetail|null $spbDet */
                    $spbDet = $spbByKeyFull->get($keyFull) ?? $spbByKey->get($keyBase);
                    if (!$spbDet) continue;

                    $ordered      = (float) ($spbDet->qty ?? 0);
                    $issuedSoFar  = (float) ($spbDet->issue_qty ?? 0);
                    $openSoFar    = (float) ($spbDet->spb_openqty ?? max($ordered - $issuedSoFar, 0));

                    // Return: kurangi issue_qty, tambah openqty
                    $newIssued = $this->clamp(($issuedSoFar - $qty), 0.0, $ordered);
                    $delta     = $issuedSoFar - $newIssued; // real yang dikembalikan
                    $newOpen   = $this->clamp(($openSoFar + $delta), 0.0, $ordered);

                    $spbDet->issue_qty       = $newIssued;
                    $spbDet->spb_openqty     = $newOpen;

                    // completeqty turunkan sesuai delta (jika semantics kamu begitu)
                    $spbDet->spb_completeqty = $this->clamp(($spbDet->spb_completeqty ?? 0) - $delta, 0.0, $ordered);

                    // Status detail
                    if ($newOpen <= 0.0000001) {
                        $spbDet->status = 'C';
                    } else {
                        $spbDet->status = 'P';
                    }

                    $spbDet->updated_by = $uname;
                    $spbDet->save();

                    $sumReturnThisDoc += $delta;
                }

                // ===== Recalculate header SPB totals
                $totals = TrSPBdetail::selectRaw("
                        COALESCE(SUM(qty),0)              AS total_spbqty,
                        COALESCE(SUM(spb_openqty),0)      AS total_spbopenqty,
                        COALESCE(SUM(issue_qty),0)        AS total_issueqty,
                        COALESCE(SUM(spb_completeqty),0)  AS total_completeqty
                    ")
                    ->where('spbid', $iss->spbid)
                    ->first();

                $spb->totalspbqty       = (float) $totals->total_spbqty;
                $spb->totalspbopenqty   = (float) $totals->total_spbopenqty;
                $spb->totalissueqty     = (float) $totals->total_issueqty;
                $spb->totalcompleteqty  = (float) $totals->total_completeqty;

                // Status SPB setelah return
                if ($spb->totalspbopenqty <= 0.0000001) {
                    $spb->status       = 'C';
                    $spb->completed_by = $uname;
                    // $spb->completed_at = $now; // jika kolom ada
                } else {
                    $spb->status = 'P';
                }
                $spb->updated_by = $uname;
                $spb->save();

                // ===== Update header Issue (return)
                $iss->status               = 'C';
                $iss->totalreturnissueqty  = (float) (($iss->totalreturnissueqty ?? 0) + $sumReturnThisDoc);
                $iss->completed_by         = $uname;
                // $iss->completed_at          = $now; // jika kolom ada
                $iss->updated_by           = $uname;
                $iss->save();

                return response()->json([
                    'ok'      => true,
                    'message' => 'Return berhasil di-approve & SPB diperbarui.',
                    'data'    => [
                        'type'                   => $iss->issuetype,
                        'issueid'                => $iss->issueid,
                        'spbid'                  => $iss->spbid,
                        'returned_issue_qty'     => $sumReturnThisDoc,
                        'spb_total_issueqty'     => (float) $spb->totalissueqty,
                        'spb_total_openqty'      => (float) $spb->totalspbopenqty,
                        'spb_status'             => $spb->status,
                    ],
                ]);
            }

            // issuetype selain 'issue' / 'return'
            return response()->json([
                'ok'      => false,
                'message' => 'Tipe issue tidak dikenali.'
            ], 422);
        });
    }

    /**
     * Helper kecil supaya bisa dipakai di closure di atas
     */
    private function clamp(float $val, float $min, float $max): float
    {
        return max($min, min($max, $val));
    }




    public function createReturn(Request $request)
    {
        $eid = (string) $request->query('iss', '');
        $id  = Hashids::decode($eid)[0] ?? null;
        abort_if(!$id, 404);

        // Header issue asal (tipe bebas; kita cuma mau referensinya)
        $iss = TrIssue::findOrFail($id);

        // Detail dari issue asal (yang qty_received-nya menjadi dasar perhitungan)
        $origDetails = TrIssuedetail::select([
            'id',
            'issueid',
            'issue_no',
            'inventoryid',
            'inventory_descr',
            'uom',
            'siteid',

            // Pastikan sama-sama numeric
            DB::raw("COALESCE((qty_received)::numeric, 0)::numeric AS qty_received"),
            DB::raw("COALESCE((base_qty_received)::numeric, 0)::numeric AS base_qty_received"),

            // Kolom multiplier sering disimpan varchar → kosongkan jadi NULL, lalu cast ke int, lalu COALESCE 1
            DB::raw("COALESCE(NULLIF(type_multiplier, '')::int, 1) AS type_multiplier"),
            DB::raw("COALESCE(NULLIF(base_multiplier, '')::int, 1) AS base_multiplier"),
        ])
        ->where('issueid', $iss->issueid)
        ->orderBy('issue_no')
        ->get();


        // Total qty_return yang SUDAH dibuat dari semua dokumen 'return' yang refer ke issue ini
        // Asumsi: dokumen pengembalian (return) disimpan di TrIssue dengan issuetype='return'
        // dan TrIssuedetail.qty_return berisi qty return per item.
        $returnedAgg = TrIssuedetail::query()
            ->select([
                'inventoryid',
                'uom',
                'siteid',
                DB::raw('SUM(COALESCE(qty_return,0)) AS sum_returned')
            ])
            ->whereIn('issueid', function($q) use ($iss) {
                $q->select('issueid')
                ->from('tr_issue') // tabel TrIssue
                ->where('issuetype', 'return')
                ->where('ref_issueid', $iss->issueid);
            })
            ->groupBy('inventoryid','uom','siteid')
            ->get()
            ->keyBy(function($r){
                return ($r->inventoryid ?? '').'|'.($r->uom ?? '').'|'.($r->siteid ?? '');
            });

        // Hitung sisa return per baris asal; tampilkan hanya yang masih > 0
        $details = $origDetails->map(function($row) use ($returnedAgg){
                $key = ($row->inventoryid ?? '').'|'.($row->uom ?? '').'|'.($row->siteid ?? '');
                $sudahReturn = (float) optional($returnedAgg->get($key))->sum_returned ?? 0.0;

                $qtyReceived = (float) $row->qty_received;
                $sisa        = max($qtyReceived - $sudahReturn, 0);

                // Agar view bisa langsung pakai $d->qty sebagai 'qty yang masih bisa direturn'
                $row->qty_sisa_return = $sisa;
                $row->qty             = $sisa; // kompatibel dengan view yg pakai $d->qty
                return $row;
            })
            ->filter(fn($r) => $r->qty_sisa_return > 0)
            ->values();

        // Jika semua item sudah habis direturn, optional: tampilkan info
        if ($details->isEmpty()) {
            return back()->with('warning', 'Semua item pada issue ini sudah tidak memiliki sisa untuk di-return.');
        }

        // Kirimkan juga ref_issueid untuk hidden input di form
        $ref_issueid = $iss->issueid;

        // Tampilkan view input return (qty_return), hidden: ref_issueid
        return view('pages.issue.return_create', [
            'iss'             => $iss,
            'details'         => $details,
            'eid'             => $eid,
            'ref_issueid'  => $ref_issueid,
        ]);
    }

    // Simpan dokumen return
    public function storeReturn(Request $request)
    {
        $user = $request->user();
        $username = $user->username ?? 'system';

        $eid = (string)$request->input('iss', '');
        $id  = Hashids::decode($eid)[0] ?? null;
        abort_if(!$id, 404);

        $src = TrIssue::findOrFail($id); // issue sumber (GR)
        $qtyInput = (array)$request->input('qty_return', []); // [detail_id => qty]
        $notes    = (string)$request->input('return_note', '');

        // minimal satu qty > 0
        $hasAny = false;
        foreach ($qtyInput as $k => $v) {
            $q = (float)str_replace(',', '.', (string)$v);
            if ($q > 0) { $hasAny = true; break; }
        }
        if (!$hasAny) {
            return back()->withErrors(['Minimal satu baris Qty Return > 0.'])->withInput();
        }

        DB::beginTransaction();
        try {
            // === Autonumber: RTYYMM#### (ubah doctype bila perlu)
            $doctype = 'IS';
            $now   = Carbon::now();
            $year  = (int)$now->year;
            $month = str_pad($now->month, 2, '0', STR_PAD_LEFT);

            $autonbr = Autonbr::lockForUpdate()
                ->where('doctype', $doctype)->where('year', $year)->where('month', $month)
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
                $urut = ($autonbr->number ?? 0) + 1;
                $autonbr->update(['number' => $urut]);
            }

            $issueid = $doctype . substr($year,2) . $month . sprintf('%04d', $urut);

            // === Header return (copy dari sumber)
            $hdr = new TrIssue();
            $hdr->issueid        = $issueid;
            $hdr->issuedate       = $now->toDateString();
            // agar muncul di tab Return Jobs sesuai filter kamu:
            $hdr->issuetype       = 'return'; // <— sesuai filter returnjobs (status C + issuetype='issue')
            $hdr->spbid             = $src->spbid;
            $hdr->ref_issueid    = $src->issueid;            // <— penting
            $hdr->cpny_id           = $src->cpny_id;
            $hdr->csid              = $src->csid;
            $hdr->sppbjktid         = $src->sppbjktid;
            $hdr->department_id     = $src->department_id;
            $hdr->user_peminta      = $src->user_peminta;
            $hdr->issuenote       = $notes;
            $hdr->vendorid          = $src->vendorid;
            $hdr->vendorname        = $src->vendorname;
            $hdr->totalqty_received = 0; // untuk return kita isi setelah loop sbg total qty return
            $hdr->status            = 'P'; // langsung complete, atau 'P' kalau mau ada approval
            $hdr->created_by        = $username;
            $hdr->created_at        = $now;
            $hdr->save();

            // === Detail return: simpan hanya baris qty_return > 0
            $srcDetails = TrIssuedetail::where('issueid', $src->issueid)->get()->keyBy('id');
            $line  = 0; $totalReturn = 0.0;

            foreach ($qtyInput as $detailId => $raw) {
                $qty = (float)str_replace(',', '.', (string)$raw);
                if ($qty <= 0) continue;          // ⬅️ hanya simpan baris > 0
                $srcDet = $srcDetails[$detailId] ?? null;
                if (!$srcDet) continue;

                $line++;

                $det = new TrIssuedetail();
                $det->issueid              = $issueid;
                $det->issue_no              = $line;

                $det->spbid                   = $src->spbid;
                $det->spb_no                   = $srcDet->spb_no;

                $det->csid                    = $src->csid;
                $det->cs_no                   = $srcDet->cs_no;
                $det->sppbjktid               = $src->sppbjktid;
                $det->sppbjktid_no            = $srcDet->sppbjktid_no;

                $det->inventory_type          = $srcDet->inventory_type;
                $det->inventoryid             = $srcDet->inventoryid;
                $det->inventory_descr         = $srcDet->inventory_descr;
                $det->qtyordered              = $srcDet->qtyordered;
                $det->uom                     = $srcDet->uom;

                // base
                $det->type_multiplier         = null;
                $det->base_multiplier         = 1;
                $det->base_qty                = 0; // return: base qty utama tidak dipakai
                $det->base_uom                = $srcDet->uom;

                // harga (copy)
                $det->unitcost                = $srcDet->unitcost;
                $det->taxcodeid               = $srcDet->taxcodeid;
                $det->taxamt                  = $srcDet->taxamt;
                $det->totalcost               = $srcDet->totalcost;

                $det->issuetype             = $hdr->issuetype;

                // open ordered (tidak relevan utk return)
                $det->qty_open_ordered        = 0;
                $det->base_qty_open_ordered   = 0;

                // return qty
                $det->qty_received            = 0;
                $det->base_qty_received       = 0;

                $det->qty_return              = $qty;
                $det->base_qty_return         = $qty;

                $det->ref_issueid          = $src->issueid;  // <— referensi

                // budget (copy)
                $det->budget_perspbst          = $srcDet->budget_perspbst;
                $det->budget_cpny_id          = $srcDet->budget_cpny_id;
                $det->budget_business_unit_id = $srcDet->budget_business_unit_id;
                $det->budget_department_fin_id= $srcDet->budget_department_fin_id;
                $det->budget_account_id       = $srcDet->budget_account_id;
                $det->budget_activity_id      = $srcDet->budget_activity_id;
                $det->budget_activity_descr   = $srcDet->budget_activity_descr;

                $det->status                  = 'C';
                $det->created_by              = $username;
                $det->created_at              = $now;
                $det->save();

                $totalReturn += $qty;
            }

            if ($totalReturn <= 0) {
                DB::rollBack();
                return back()->withErrors(['Minimal satu baris Qty Return > 0.'])->withInput();
            }

            // simpan total return di header (pakai kolom totalqty_received sebagai penampung)
            $hdr->totalqty_received = $totalReturn;
            $hdr->save();
    
            $deptid = $src->department_id;
            $cpnyid = $src->cpny_id;
            
            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr'        => $issueid,
                    'doctype'       => $doctype,
                    'cpnyid'        => $cpnyid,
                    'departementid' => $deptid,                    
                    'base_folder'   => 'att-purchasing-app/'.strtolower($doctype),
                    'created_by'    => $user->username,
                ];

                $files = (array) $request->file('attachments');

                try {
                    $uploader = app(TrAttachmentController::class);
                    $uploadResult = $uploader->uploadInternal($meta, $files);
                    // tidak return di sini!
                } catch (\Throwable $e) {
                    \DB::rollBack();
                    return response()->json([
                        'message' => 'Failed to create PB',
                        'error'   => 'Gagal upload attachment: '.$e->getMessage(),
                    ], 500);
                }
            } else {
                $uploadResult = null; // tidak ada attachment
            }

            DB::commit();

            return redirect()->route('issuelist')
                ->with('success', "Return {$issueid} created from {$src->issueid}. Total Qty Return: {$totalReturn}");
        } catch (\Throwable $e) {
            DB::rollBack();
            respbrt($e);
            return back()->withErrors([config('app.debug') ? $e->getMessage() : 'Failed to create Return'])->withInput();
        }
    }



    


}
