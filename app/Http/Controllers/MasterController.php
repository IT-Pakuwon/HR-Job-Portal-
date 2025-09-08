<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\MsInventoryPG;
use App\Models\MsInventoryStockPG;
use App\Models\MsRequestType;
use App\Models\MsLocationPG;
use App\Models\MsSubLocationPG;
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
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\BqDetailTempImport; 

class MasterController extends Controller
{

    public function InventoryList(Request $request)
    {
        $type    = strtoupper($request->get('type', 'STOCK')); // STOCK | NONSTOCK | JASA | ALL
        $search  = trim($request->get('search', ''));
        $page    = max((int) $request->get('page', 1), 1);
        $perPage = max((int) $request->get('per_page', 10), 1);

        // Selalu pakai MsInventoryPG
        $query = MsInventoryPG::query()
            ->select(
                'inventoryid',
                'inventory_descr',
                'stock_unit',
                'item_type',
                'item_category',
                // 'account_id',     // pastikan kolom ada di MsInventoryPG
                'purchase_unit',  // untuk dikirim ke view
                'item_sub_type',                
            );

        // If hanya untuk STOCK, else untuk tipe lain
        if ($type === 'STOCK') {
            $query->where('item_sub_type', 'STOCK');
        } else {
            // semua selain STOCK
            $query->where('item_sub_type', '<>', 'STOCK');
        }
        
        // Pencarian (Postgres ILIKE)
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('inventoryid',      'ilike', "%{$search}%")
                ->orWhere('inventory_descr','ilike', "%{$search}%")
                ->orWhere('stock_unit',     'ilike', "%{$search}%")
                ->orWhere('purchase_unit',  'ilike', "%{$search}%");
            });
        }

        $total = (clone $query)->count();

        $rows = $query->orderBy('inventory_descr')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return response()->json([
            'data'     => $rows,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage,
        ]);
    }

 
    public function RequestType(Request $request)
    {
        $doctype = $request->query('doctype');
        
        if (!$doctype) {
            return response()->json([
                'message' => 'Parameter "doctype" wajib diisi.'
            ], 400);
        }

        // Opsional: normalisasi huruf besar
        $doctype = strtoupper(trim($doctype));

        $rows = MsRequestType::query()
            ->select('requesttypeid', 'requesttype_name')
            ->where('doctype', $doctype)
            ->where('status', 'A')
            ->orderBy('requesttype_name')
            ->get();
            
        return response()->json(['data' => $rows]);
    }

    public function Location(Request $request)
    {
        $cpnyid  = $request->get('cpnyid');
        $search  = trim($request->get('search', ''));
        $page    = max((int)$request->get('page', 1), 1);
        $perPage = max((int)$request->get('per_page', 10), 1);

        if (!$cpnyid) {
            return response()->json(['data' => [], 'total' => 0, 'page' => $page, 'per_page' => $perPage]);
        }

        // Sesuaikan nama kolom Mslocation kamu:
        // asumsi: cpny_id, location_id, location_name, status
        $q = MslocationPG::query()
            ->where('cpny_id', $cpnyid)
            ->where('status', 'A');

        if ($search !== '') {
            $q->where(function ($w) use ($search) {
                $w->where('location_id', 'ilike', "%{$search}%")
                  ->orWhere('location_name', 'ilike', "%{$search}%");
            });
        }

        $total = (clone $q)->count();

        $rows = $q->orderBy('location_name')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get(['location_id', 'location_name']);

        return response()->json([
            'data'     => $rows,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage,
        ]);
    }

    public function SubLocation(Request $request)
    {
        $cpnyid     = $request->get('cpnyid');
        $location_id = $request->get('location_id');
        $search     = trim($request->get('search', ''));
        $page       = max((int)$request->get('page', 1), 1);
        $perPage    = max((int)$request->get('per_page', 10), 1);

        if (!$cpnyid || !$location_id) {
            return response()->json([
                'data' => [], 'total' => 0, 'page' => $page, 'per_page' => $perPage
            ]);
        }

        // SESUAIKAN nama kolom:
        // asumsi: cpny_id, location_id, sub_location_id, sub_location_name, status
        $q = MsSubLocationPG::query()
            ->where('cpny_id', $cpnyid)
            ->where('location_id', $location_id)
            ->where('status', 'A');

        if ($search !== '') {
            $q->where(function ($w) use ($search) {
                $w->where('sub_location_id', 'ilike', "%{$search}%")
                  ->orWhere('sub_location_name', 'ilike', "%{$search}%");
            });
        }

        $total = (clone $q)->count();

        $rows = $q->orderBy('sub_location_name')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get([
                'sub_location_id', 'sub_location_name'
            ]);

        return response()->json([
            'data'     => $rows,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage,
        ]);
    }

    public function DepartmentFin(string $cpny_id)
    {
        // dd("cpny_id: {$cpny_id}");
        // Sesuaikan kolom di model/DB: cpny_id, department_fin_id, department_name
        $departments = DepartmentFin::query()
            ->where('cpny_id', $cpny_id)
            ->orderBy('department_name')
            ->get(['department_fin_id', 'department_name']);

        return response()->json($departments);
    }

    public function CoaBudget(Request $request)
    {
        $cpnyid   = $request->get('cpnyid');
        $deptid   = $request->get('deptid');
        $perpost  = $request->get('perpost'); // ⬅️ ambil perpost (tahun)
        $search   = trim($request->get('search', ''));
        $page     = max((int)$request->get('page', 1), 1);
        $perPage  = max((int)$request->get('per_page', 10), 1);

        if (!$cpnyid || !$deptid) {
            return response()->json([
                'data' => [], 'total' => 0, 'page' => $page, 'per_page' => $perPage
            ]);
        }

        // Ambil budget aktif untuk company+dept (dan perpost jika ada)
        $budget = Budget::where('status', 'C')
            ->where('cpny_id', $cpnyid)
            ->where('department_fin_id', $deptid)
            ->when($perpost, function ($q) use ($perpost) {
                $q->where('perpost', $perpost);
            })
            ->first();


        $budgetDetail = BudgetDetail::query()
            ->when($budget, function ($q) use ($budget) {
                $q->where('budget_id', $budget->budget_id);
            })
            ->where('cpny_id', $cpnyid)
            ->where('department_fin_id', $deptid)
            // ->where('status', 'A') // aktifkan jika ada kolom status
            ->when($perpost, function ($q) use ($perpost) {
                $q->where('perpost', $perpost);
            });

        if ($search !== '') {
            $budgetDetail->where(function ($w) use ($search) {
                $w->where('account_id',     'ilike', "%{$search}%")
                ->orWhere('activity_detail','ilike', "%{$search}%")
                ->orWhere('totalbudget',  'ilike', "%{$search}%");
            });
        }

        $total = (clone $budgetDetail)->count();

        $rows = $budgetDetail->orderBy('activity_detail')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get(['account_id', 'activity_id', 'activity_detail', 'totalbudget','business_unit_id','department_fin_id']);

        return response()->json([
            'data'     => $rows,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage,
        ]);
    }

    public function UomInventory(Request $req)
    {
        $inventoryid = $req->get('inventoryid');
        $search      = trim($req->get('search', ''));
        $page        = max(1, (int)$req->get('page', 1));
        $perPage     = max(1, (int)$req->get('per_page', 10));

        if (!$inventoryid) {
            return response()->json([
                'data' => [],
                'total' => 0,
                'page' => $page,
                'per_page' => $perPage,
            ]);
        }

        $q = MsUom::query()->where('inventoryid', $inventoryid);

        if ($search !== '') {
            $q->where(function($w) use ($search) {
                $w->where('from_unit', 'ilike', "%{$search}%")
                ->orWhere('to_unit', 'ilike', "%{$search}%");
            });
        }

        $total  = $q->count();
        $items  = $q->orderBy('from_unit')->orderBy('to_unit')
                    ->skip(($page-1)*$perPage)->take($perPage)->get([
                        'inventoryid','from_unit','to_unit','unitmultdiv','unitrate'
                    ]);

        return response()->json([
            'data'     => $items,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage,
        ]);
    }

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

    public function import(Request $request)
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

           
             return $idx
                ? redirect()->route('bqsppj.edit', $idx)
                            ->with('success', 'Data berhasil di‑import (edit mode).')
                : redirect()->route('bqs.create')
                            ->with('success', 'Data berhasil di‑import.');
            
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



}
