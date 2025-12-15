<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\MsInventory;
use App\Models\MsInventoryStockPG;
use App\Models\MsRequestType;
use App\Models\MsLocation;
use App\Models\MsSubLocation;
use App\Models\DepartmentFin;
use App\Models\Autonbr;
use App\Models\BudgetDetail;
use App\Models\Budget;
use App\Models\MsUom;
use App\Models\TrSPPJ;
use App\Models\TrSPPJdetail;
use App\Models\Bq;
use App\Models\BqDetail;
use App\Models\BqDetailTemp;
use App\Models\Attachment;
use App\Models\MsKendaraan;
use App\Models\User;
use App\Models\MsTenant;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\BqDetailTempImport; 

class BqController extends Controller
{

    
    public function createBQ($id)
    {       
        $user = request()->user();     
        $sppj = TrSPPJ::findOrFail($id);  
       
        $temp_id = session('import_temp_id'); // ambil dari session

        $tempData = [];
        if ($temp_id) {
            $tempData = BqDetailTemp::where('temp_id', $temp_id)->get();
        }

       
        return view('pages.sppjs.createbqs', compact('sppj','tempData','temp_id'));
    }

    public function importCreate(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'file'    => 'required|mimes:xlsx,xls,csv',
            'sppjtid' => 'required',
        ]);

        try {
            $username = Auth::user()->username ?? 'system';
            $temp_id  = (string) Str::uuid();

            // Bersihkan temp milik user agar batch tidak tercampur
            BqDetailTemp::where('created_by', $username)->delete();

            $idx = $request->input('idx');
            $sppjtid = $request->input('sppjtid');

            // Import Excel ke tr_bq_detail_temp
            Excel::import(
                new BqDetailTempImport($temp_id, $sppjtid),
                $request->file('file')
            );

            // Simpan temp_id ke session untuk dipakai di halaman create
            session(['import_temp_id' => $temp_id]);

            // ⬇️ Selalu redirect ke create
            return redirect()
                ->route('bqs.create', $idx)
                ->with('success', 'Data berhasil di-import.');
        } catch (\Throwable $e) {
            // opsional: report($e);
            return back()
                ->withInput()
                ->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }


    public function importEdit(Request $request)
    {
        $request->validate([
            'file'     => 'required|mimes:xlsx,xls,csv',
            'sppjtid'  => 'required', // dari hidden input di form
            // 'bqid'   => 'nullable'  // kalau suatu saat kamu kirim bqid juga
        ]);

        try {
            $username = Auth::user()->username ?? 'system';
            $temp_id  = (string) Str::uuid();
            
            // Bersihkan temp milik user agar tidak tercampur batch sebelumnya
            BqDetailTemp::where('created_by', $username)->delete();

            $idx = $request->input('idx');
            $sppjtid = $request->input('sppjtid');
            // $bqid    = $request->input('bqid'); // opsional
            
            // Import Excel ke tr_bq_detail_temp
            Excel::import(
                new BqDetailTempImport($temp_id, $sppjtid),
                $request->file('file')
            );

            // Simpan temp_id ke session untuk dipakai di createBQ()
            session(['import_temp_id' => $temp_id]);

           return redirect()
                ->route('bqsppj.edit', $idx)
                ->with('success', 'Data berhasil di‑import (edit mode).');
            //  return $idx
            //     ? redirect()->route('bqsppj.edit', $idx)
            //                 ->with('success', 'Data berhasil di‑import (edit mode).')
            //     : redirect()->route('bqs.create')
            //                 ->with('success', 'Data berhasil di‑import.');
            
            return back()->with('success', 'Data BQ berhasil di-import.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal import: '.$e->getMessage());
        }
    }

    public function storeBQ(Request $request)
    {
        $request->validate([
            'temp_id' => 'required',
            // 'bq_type' => 'nullable|string|max:20',
        ]);

        $temp_id = $request->input('temp_id');

        // Ambil batch temp
        $tempData = BqDetailTemp::where('temp_id', $temp_id)
            ->orderBy('bq_line_no', 'asc')
            ->get();
        if ($tempData->isEmpty()) {
            return response()->json(['message' => 'Tidak ada data BQ import ditemukan!'], 422);
        }
        $tempHead  = $tempData->first();

        $dt       = Carbon::now();
        $datenow  = $dt->format('Y-m-d');
        $year     = $dt->year;
        $month    = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
        $username = Auth::user()->username ?? 'system';

        // Kebutuhan header
        $doctype  = 'BQ';
        $sppjtid  = $tempHead->sppjtid ?? $request->input('sppjtid'); // string SPPJID (mis. SPPJ-xxxxx)
        $bq_type  = $request->input('bq_type', 'SPPJ'); // default

        // Ambil cpny_id dari SPPJ (kalau kolom BQ wajib)
        $cpny_id = null;
        if ($sppjtid) {
            $sppj = TrSPPJ::where('sppjid', $sppjtid)
                        ->orWhere('id', $request->input('idx')) // kalau kamu kirim idx juga
                        ->first();
            $cpny_id = $sppj->cpny_id ?? $sppj->cpnyid ?? null;
        }

        // Grand total header
        $grandMat  = $tempData->sum(fn($r) => (float) ($r->total_est_material_price ?? 0));
        $grandJasa = $tempData->sum(fn($r) => (float) ($r->total_est_jasa_price ?? 0));

        DB::beginTransaction(); // kalau semua di PG, bisa pakai DB::connection('pgsql')->beginTransaction();
        try {
            // ===== Autonumber untuk BQID =====
            $autonbr = Autonbr::lockForUpdate()
                ->where('doctype', $doctype)
                ->where('year', $year)
                ->where('month', $month)
                ->where('status', 'A')
                ->first();

            if (!$autonbr) {
                $autonbr = Autonbr::create([
                    'doctype' => $doctype,
                    'year'    => $year,
                    'month'   => $month,
                    'status'  => 'A',
                    'number'  => 1,
                ]);
                $urutan = 1;
            } else {
                $urutan = $autonbr->number + 1;
                $autonbr->number = $urutan;
                $autonbr->save();
            }

            $tglbln = substr($year, 2) . $month;
            $bqid   = $doctype . $tglbln . sprintf('%03d', $urutan);

            $sppj->bqid = $bqid;
            $sppj->save();

            // ===== Insert HEADER: tr_bq =====
            $bq = Bq::create([
                'bqid'                           => $bqid,
                'sppjtid'                        => $sppjtid,
                'cpny_id'                        => $cpny_id,
                'bq_type'                        => $bq_type,
                'grand_total_est_material_price' => $grandMat,
                'grand_total_est_jasa_price'     => $grandJasa,
                'status'                         => 'P',
                'created_by'                     => $username,
                'updated_by'                     => $username,
            ]);

            // ===== Insert DETAIL: tr_bq_detail =====
            $seq = 1; // nomor urut dimulai dari 1
            foreach ($tempData as $row) {
                BqDetail::create([
                    'bqid'                     => $bqid,
                    'sppjtid'                  => $row->sppjtid,
                    'bq_no'                    => $seq++,            // <<=== no urut
                    'bq_line_no'               => $row->bq_line_no,  // tetap simpan line no asli jika diperlukan
                    'bq_descr'                 => $row->bq_descr,
                    'qty'                      => $row->qty,
                    'uom'                      => $row->uom,
                    'est_material_price'       => $row->est_material_price,
                    'total_est_material_price' => $row->total_est_material_price,
                    'est_jasa_price'           => $row->est_jasa_price,
                    'total_est_jasa_price'     => $row->total_est_jasa_price,
                    'status'                   => 'P',
                    'created_by'               => $username,
                    'updated_by'               => $username,
                ]);
            }


            // ===== Hapus temp batch =====
            BqDetailTemp::where('temp_id', $temp_id)->delete();

            // ===== Attachments (optional): simpan ke /public/attachments/{year} =====
           
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    if (!$file || !$file->isValid()) continue;

                    // ambil nama asli dan extension
                    $original   = $file->getClientOriginalName();
                    $baseName   = pathinfo($original, PATHINFO_FILENAME);
                    $ext        = strtolower($file->getClientOriginalExtension());

                    // sanitasi nama untuk disimpan di kolom "name"
                    $safeName   = Str::limit(preg_replace('/[^A-Za-z0-9_\- ]/', '', $baseName), 120, '');

                    // buat nama file unik untuk disimpan di folder & kolom "attachfile"
                    $unique     = md5(uniqid('', true));
                    $storedName = $unique . '-' . ($safeName !== '' ? Str::slug($safeName, '_') : 'photo') . '.' . $ext;

                    // pastikan folder ada
                    $folder = public_path('attachments/' . $year);
                    if (!is_dir($folder)) {
                        @mkdir($folder, 0755, true);
                    }

                    // pindahkan file
                    $file->move($folder, $storedName);

                    // simpan ke DB tanpa mass assignment
                    $attach = new Attachment();
                    $attach->docid        = $bqid;      // relasi ke dokumen BQ
                    $attach->name         = $safeName;  // nama (tanpa ext)
                    $attach->attachfile   = $storedName; // nama file di server (dengan ext)
                    $attach->status       = 'A';
                    $attach->extention    = $ext;        // kolommu memang "extention"
                    $attach->created_user = $username;
                    $attach->save();
                }
            }


            DB::commit();
            return response()->json(['success' => true, 'bq' => $bq]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal menyimpan BQ', 'message' => $e->getMessage()], 500);
        }
    }

    public function updateBQ(Request $request, int $id)
    {
        // temp_id boleh kosong → artinya tidak ada import baru (hanya simpan &/atau tambah lampiran)
        $request->validate([
            'temp_id' => 'nullable|string',
            'bq_type' => 'nullable|string|max:20',
            // 'attachments.*' => 'file|mimes:jpg,jpeg,png,webp,gif,bmp,svg|max:5120', // opsional validasi file
        ]);

        $username = Auth::user()->username ?? 'system';
        $now      = Carbon::now();

        $bq = Bq::findOrFail($id);
        $bqid    = $bq->bqid;                 // <-- dipertahankan (tidak generate baru)
        $sppjtid = $bq->sppjtid ?? $request->input('sppjtid');

        // Ambil temp data jika ada
        $tempId   = $request->input('temp_id');
        $tempData = collect();
        if ($tempId) {
            $tempData = BqDetailTemp::where('temp_id', $tempId)
                        ->orderBy('bq_line_no', 'asc')
                        ->get();
        }

        DB::beginTransaction();
        try {
            // ===================== HEADER =====================
            // Hitung grand total:
            //  - jika ada tempData → pakai tempData
            //  - jika tidak ada → hitung dari detail existing agar tetap konsisten
            if ($tempData->isNotEmpty()) {
                $grandMat  = $tempData->sum(fn($r) => (float) ($r->total_est_material_price ?? 0));
                $grandJasa = $tempData->sum(fn($r) => (float) ($r->total_est_jasa_price ?? 0));
            } else {
                $grandMat  = (float) BqDetail::where('bqid', $bqid)->sum('total_est_material_price');
                $grandJasa = (float) BqDetail::where('bqid', $bqid)->sum('total_est_jasa_price');
            }

            // Optional: update cpny_id dari SPPJ jika ingin sinkron lagi (bisa di-skip)
            // $cpny_id = $bq->cpny_id;
            // if ($sppjtid) {
            //     $sppj    = TrSPPJ::where('sppjid', $sppjtid)->first();
            //     $cpny_id = $sppj->cpny_id ?? $cpny_id;
            // }

            $bq->grand_total_est_material_price = $grandMat;
            $bq->grand_total_est_jasa_price     = $grandJasa;
            if ($request->filled('bq_type')) {
                $bq->bq_type = $request->input('bq_type');
            }
            $bq->updated_by = $username;
            $bq->updated_at = $now;
            $bq->save();

            // ===================== DETAIL (replace jika ada temp) =====================
            if ($tempData->isNotEmpty()) {
                // hapus semua detail lama bqid ini
                BqDetail::where('bqid', $bqid)->delete();

                // insert ulang dari temp (nomor urut bq_no dimulai 1)
                $seq = 1;
                foreach ($tempData as $row) {
                    BqDetail::create([
                        'bqid'                     => $bqid,
                        'sppjtid'                  => $row->sppjtid,
                        'bq_no'                    => $seq++,
                        'bq_line_no'               => $row->bq_line_no,
                        'bq_descr'                 => $row->bq_descr,
                        'qty'                      => $row->qty,
                        'uom'                      => $row->uom,
                        'est_material_price'       => $row->est_material_price,
                        'total_est_material_price' => $row->total_est_material_price,
                        'est_jasa_price'           => $row->est_jasa_price,
                        'total_est_jasa_price'     => $row->total_est_jasa_price,
                        'status'                   => 'P',
                        'created_by'               => $username,
                        'updated_by'               => $username,
                    ]);
                }

                // bersihkan temp batch setelah dipakai
                BqDetailTemp::where('temp_id', $tempId)->delete();
            }

            // ===================== ATTACHMENTS (tambahan) =====================
            if ($request->hasFile('attachments')) {
                $year = $now->year;
                $folder = public_path('attachments/' . $year);
                if (!is_dir($folder)) {
                    @mkdir($folder, 0755, true);
                }

                foreach ($request->file('attachments') as $file) {
                    if (!$file || !$file->isValid()) continue;

                    $original   = $file->getClientOriginalName();
                    $baseName   = pathinfo($original, PATHINFO_FILENAME);
                    $ext        = strtolower($file->getClientOriginalExtension());

                    $safeName   = Str::limit(preg_replace('/[^A-Za-z0-9_\- ]/', '', $baseName), 120, '');
                    $unique     = md5(uniqid('', true));
                    $storedName = $unique . '-' . ($safeName !== '' ? Str::slug($safeName, '_') : 'photo') . '.' . $ext;

                    $file->move($folder, $storedName);

                    $attach = new Attachment();
                    $attach->docid        = $bqid;
                    $attach->name         = $safeName;
                    $attach->attachfile   = $storedName;
                    $attach->status       = 'A';
                    $attach->extention    = $ext;
                    $attach->created_user = $username;
                    $attach->save();
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'bq' => $bq]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'error'   => 'Gagal mengupdate BQ',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function listKendaraan(Request $request)
    {
        $request->validate([
            'search'   => 'nullable|string',
            'page'     => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:500',
        ]);

        $search  = $request->search ?? '';
        $page    = (int)($request->page ?? 1);
        $perPage = (int)($request->per_page ?? 100);

        $q = MsKendaraan::query();

        if ($search !== '') {
            $q->where(function($w) use ($search) {
                $w->where('no_polisi', 'ILIKE', "%{$search}%")
                  ->orWhere('namakendaraan', 'ILIKE', "%{$search}%")
                  ->orWhere('pemilikkendaraan', 'ILIKE', "%{$search}%");
            });
        }

        $total = (clone $q)->count();
        $rows  = $q->orderBy('no_polisi')
                   ->forPage($page, $perPage)
                   ->get(['no_polisi','namakendaraan','pemilikkendaraan']);

        return response()->json([
            'data'     => $rows,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage,
        ]);
    }

    public function tenants(Request $req)
    {
        $q        = trim($req->get('q', ''));
        $page     = max(1, (int) $req->get('page', 1));
        $perPage  = max(1, min(50, (int) $req->get('per_page', 10)));

        $query = MsTenant::query();

        // Asumsi kolom: tenant (nama), lantai (atau floor), unit (atau unit_no)
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('tenant', 'ILIKE', "%{$q}%")
                  ->orWhere('lantai', 'ILIKE', "%{$q}%")
                  ->orWhere('unit', 'ILIKE', "%{$q}%");
            });
        }

        $total = (clone $query)->count();

        $rows = $query
            ->orderBy('tenant')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $data = $rows->map(function ($r) {
            $floor = $r->lantai ?? $r->floor ?? '';
            $unit  = $r->unit ?? $r->unit_no ?? '';
            return [
                'id'         => $r->id,                   // sesuaikan PK
                'text'       => $r->tenant ?? '-',        // nama tenant
                'unit_label' => trim(($floor ? $floor : '') . ($unit ? (' - ' . $unit) : '')),
                'floor'      => $floor,
                'unit'       => $unit,
            ];
        });

        return response()->json([
            'data'  => $data,
            'total' => $total,
            'page'  => $page,
            'per_page' => $perPage,
        ]);
    }

    public function users(Request $req)
    {
        $q        = trim($req->get('q', ''));
        $page     = max(1, (int) $req->get('page', 1));
        $perPage  = max(1, min(50, (int) $req->get('per_page', 10)));

        $query = User::query();

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                //   ->orWhere('email', 'like', "%{$q}%")
                  ->orWhere('username', 'like', "%{$q}%");
            });
        }

        $total = (clone $query)->count();

        $rows = $query
            ->orderBy('name')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get(['id', 'name', 'email','username']);

        $data = $rows->map(function ($u) {
            return [
                'id'    => $u->id,
                'text'  => $u->name ?? $u->email ?? ('User#'.$u->id),
                'email' => $u->email,
                'username' => $u->username,
            ];
        });

        return response()->json([
            'data'  => $data,
            'total' => $total,
            'page'  => $page,
            'per_page' => $perPage,
        ]);
    }


}
