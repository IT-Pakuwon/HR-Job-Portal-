<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\TrPO;
use App\Models\TrPOdetail;
use App\Models\T_approval;
use App\Models\Attachment;
use Vinkla\Hashids\Facades\Hashids;

class PoController extends Controller
{
    public function showPo($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        // Header PO
        $po = TrPO::findOrFail($id);

        // Detail PO (pakai ponbr sebagai foreign key detail)
        $podetail = TrPOdetail::where('ponbr', $po->ponbr)
            ->orderBy('cs_no') // ganti sesuai nama kolom line kalau berbeda
            ->get();

        // Approval & Attachment pakai docid = ponbr
        $approval = T_approval::where('docid', $po->ponbr)
            ->where('status', '<>', 'X')
            ->orderBy('created_at')
            ->orderBy('aprvid')
            ->get();

        $attachment = Attachment::where('docid', $po->ponbr)
            ->where('status', 'A')
            ->get();

        // Kembalikan ke view
        // - kirim variabel baru: $po, $podetail
        // - plus alias lama ($sppb, $sppbdetail) untuk kompatibilitas view lama
        return view('pages.purchase.showpo', [
            'po'         => $po,
            'podetail'   => $podetail,
            'approval'   => $approval,
            'attachment' => $attachment,
            'hash'       => $hash,           
            'sppb'       => $po,
            'sppbdetail' => $podetail,
        ]);
    }

    public function submit(Request $req, $ponbr)
    {
        $po = TrPO::where('ponbr', $ponbr)->firstOrFail();

        if ($po->status !== 'H') {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen hanya bisa di-Submit jika status = HOLD (H).'
            ], 422);
        }

        // Terima kedua nama field: po_deliverydate (dari view lama) atau podeliverydate (kolom DB)
        $deliveryDate = $req->input('podeliverydate') ?? $req->input('po_deliverydate');

        // Validasi dinamis sesuai po type
        if (strtoupper($po->potype ?? '') === 'PB') {
            $validated = $req->validate([
                'podeliverydate'   => ['nullable','date'],   // supaya lolos kalau pakai podeliverydate
                'po_deliverydate'  => ['nullable','date'],   // dan juga po_deliverydate
            ]);

            if (empty($deliveryDate)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The podeliverydate field is required.'
                ], 422);
            }

        } else {
            $validated = $req->validate([
                'work_date_from'   => ['required','date'],
                'work_date_to'     => ['required','date','after_or_equal:work_date_from'],
                'work_days'        => ['required','integer','min:0'],
                'work_day_from'    => ['required','string'],
                'work_day_to'      => ['required','string'],
                'work_time_from'   => ['required','date_format:H:i'],
                'work_time_to'     => ['required','date_format:H:i'],
                'manpower_total'   => ['required','integer','min:0'],
                'pic_name'         => ['required','string'],
                'pic_phone'        => ['required','string'],
                'payment_method'   => ['required','string'],
                'warranty'         => ['required','string'],
            ]);
        }

        DB::transaction(function () use ($po, $req, $deliveryDate) {
            $now = Carbon::now();
            $po->submitdate = $now;
            $po->updated_by = Auth::user()->username ?? 'system';

            if (strtoupper($po->potype ?? '') === 'PB') {
                // hanya simpan tanggal delivery
                $po->podeliverydate = $deliveryDate ? Carbon::parse($deliveryDate) : null;

                // (opsional) catat sedikit ringkasan di ponote
                $po->ponote = trim(($po->ponote ? $po->ponote."\n" : '') .
                    'Delivery Date: '.Carbon::parse($deliveryDate)->format('d/m/Y'));
            } else {
                // simpan field SPK ke kolom yang tersedia di model
                $po->spkstartworkingdate = $req->input('work_date_from');
                $po->spkendtworkingdate  = $req->input('work_date_to');
                $po->spktotalday         = $req->input('work_days');

                // schedule: "Hari X s/d Y Pukul a s/d b WIB"
                $schedule = sprintf(
                    'Hari %s s/d %s Pukul %s s/d %s WIB',
                    $req->input('work_day_from'),
                    $req->input('work_day_to'),
                    $req->input('work_time_from'),
                    $req->input('work_time_to')
                );
                $po->spkworkschedule = $schedule;

                // manpower & PIC
                $po->spkmanpower = $req->input('manpower_total');
                $po->spkpic      = trim($req->input('pic_name').' || HP '.$req->input('pic_phone'));
                $po->spkwarranty = $req->input('warranty');

                // simpan "cara pembayaran" ke ponote (kolom yang ada)
                $pm = strtoupper($req->input('payment_method'));
                $po->ponote = trim(($po->ponote ? $po->ponote."\n" : '') .
                    "Cara Pembayaran: {$pm}");
            }

            // ubah status ke Purchase Order
            $po->status = 'P';
            $po->save();
        });

        return response()->json([
            'success' => true,
            'message' => 'Submit berhasil. Status berubah menjadi Purchase Order (P).'
        ]);
    }

    /** POST /po/{ponbr}/cancel-reuse */
    public function cancelReuse(Request $req, $ponbr)
    {
        $po = TrPO::where('ponbr', $ponbr)->firstOrFail();

        $data = $req->validate([
            'reason' => ['required','string']
        ]);

        $po->status     = 'R';
        $po->updated_by = Auth::user()->username ?? 'system';

        // simpan reason ke ponote (append)
        $stamp = Carbon::now()->format('d/m/Y H:i');
        $who   = Auth::user()->username ?? 'user';
        $reasonLine = "[{$stamp}] {$who} → CANCEL REUSE: ".$data['reason'];

        $po->ponote = trim(($po->ponote ? $po->ponote."\n" : '').$reasonLine);
        $po->save();

        return response()->json([
            'success' => true,
            'message' => 'Status diubah menjadi REUSE (R).'
        ]);
    }

    /** POST /po/{ponbr}/cancel */
    public function cancel(Request $req, $ponbr)
    {
        $po = TrPO::where('ponbr', $ponbr)->firstOrFail();

        $data = $req->validate([
            'reason' => ['required','string']
        ]);

        $po->status     = 'X';
        $po->updated_by = Auth::user()->username ?? 'system';

        // simpan reason ke ponote (append)
        $stamp = Carbon::now()->format('d/m/Y H:i');
        $who   = Auth::user()->username ?? 'user';
        $reasonLine = "[{$stamp}] {$who} → CANCEL: ".$data['reason'];

        $po->ponote = trim(($po->ponote ? $po->ponote."\n" : '').$reasonLine);
        $po->save();

        return response()->json([
            'success' => true,
            'message' => 'Status diubah menjadi CANCEL (X).'
        ]);
    }
}
