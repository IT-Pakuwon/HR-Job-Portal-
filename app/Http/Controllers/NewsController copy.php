<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\UserDas;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Autonbr;
use App\Models\Company;
use App\Models\Dept;
use Illuminate\Support\Facades\DB;

class NewsController extends Controller
{
    /**
     * Menampilkan halaman utama dengan DataTables
     */
    public function index()
    {
        $participant = UserDas::where('status', 'A')          
            ->get();
        // dd($participant);
        return view('pages.news.index', compact('participant'));        
        
    }

    /**
     * Mengambil data news untuk DataTables (JSON Response)
     */
    public function json()
    {
        $news = News::select(['id', 'newsid', 'title', 'newsdate', 'cpnyid', 'departementid', 'description', 'status'])
            ->orderBy('newsid', 'asc') // Mengurutkan berdasarkan newsid descending
            ->get();


        return response()->json(['data' => $news]);
    }

    public function getCompany()
    {
        $companies = Company::select('cpnyid')->get();
        return response()->json($companies);
    }

    public function getDepartement()
    {
        $departement = Dept::select('deptname')->get();       
        return response()->json($departement);
    }

 
    /**
     * Menyimpan news baru
     */
    public function store(Request $request)
    {
        // dd($request->all());
        // Validasi input
        $request->validate([
            'newsdate' => 'nullable|date',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',          
            'cpnyid' => 'nullable|string',    
            'departementid' => 'nullable|string'         
        ]);

        DB::beginTransaction();
        try {
            $datenow = Carbon::now()->format('Y-m-d');
            $datestamp = Carbon::now()->toDateTimeString();
            $dt = Carbon::now();
            $year = $dt->year;
            $month = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
            $doctype = 'NEW';

            // Ambil nomor terakhir dari tabel Autonbr
            $autonbr = Autonbr::lockForUpdate()
                ->where('doctype', $doctype)
                ->where('year', $year)
                ->where('month', $month)
                ->where('status', 'A')
                ->first();

            if (!$autonbr) {
                $autonbr = Autonbr::create([
                    'doctype' => $doctype,
                    'year' => $year,
                    'month' => $month,
                    'status' => 'A',
                    'number' => 1
                ]);
                $urutan = 1;
            } else {
                $urutan = $autonbr->number + 1;
                $autonbr->number = $urutan;
                $autonbr->save();
            }

            // Buat task ID
            $tglbln = substr($year, 2) . $month;
            $docid = $doctype . $tglbln . sprintf("%03d", $urutan);
          
            // Buat news baru
            $news = News::create([        
                'newsid' => $docid,      
                'newsdate' => $request->newsdate,
                'title' => $request->title,
                'description' => $request->description,
                'cpnyid' => $request->cpnyid,
                'departementid' => $request->departementid,
                'status' => $request->status ?? 'A'                
            ]);

            DB::commit();
            return response()->json(['success' => true, 'news' => $news]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal menyimpan news', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Mengambil data news berdasarkan ID (untuk edit)
     */
    public function edit($id)
    {
        // dd($id);
        $news = News::findOrFail($id);
        return response()->json($news);
    }

    /**
     * Mengupdate news berdasarkan ID
     */
    public function update(Request $request, $id)
    {
        // dd($request->all());
        // Validasi input
        $request->validate([
            'newsdate' => 'nullable|date',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',          
            'cpnyid' => 'nullable|string',    
            'departementid' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $news = News::findOrFail($id);
            $news->update([
                'newsdate' => $request->newsdate,
                'title' => $request->title,
                'description' => $request->description,
                'cpnyid' => $request->cpnyid,
                'departementid' => $request->departementid,
                'status' => $request->status ?? 'A'   
            ]);

            DB::commit();
            return response()->json(['success' => true, 'news' => $news]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal memperbarui news', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Menghapus news berdasarkan ID
     */
    public function destroy($id)
    {
        $news = News::findOrFail($id);
        $news->delete();

        return response()->json(['success' => true, 'message' => 'Data berhasil dihapus']);
    }
}
