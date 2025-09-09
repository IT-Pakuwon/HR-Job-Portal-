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
use App\Models\MsLocationPG;
use App\Models\MsSubLocationPG;
use App\Models\vReceivedList;
use Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;



class CanvassController extends Controller
{
     public function ReceivedList()
    {
        // Counter kartu: All & per doc_type
        $all  = vReceivedList::count();
        $sppb = vReceivedList::where('doc_type', 'SPPB')->count();
        $sppj = vReceivedList::where('doc_type', 'SPPJ')->count();
        $sppk = vReceivedList::where('doc_type', 'SPPK')->count();
        $sppt = vReceivedList::where('doc_type', 'SPPT')->count();

        return view('pages.canvass.received', compact('all', 'sppb', 'sppj', 'sppk', 'sppt'));
    }

    public function ReceivedListJson(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $search = trim((string) $request->input('search.value', ''));
        $doc    = (string) $request->query('doc', ''); // '', 'SPPB','SPPJ','SPPK','SPPT'

        $columns = [
            0 => 'doc_no',
            1 => 'assignpurchasing',
            2 => 'doc_date',
            3 => 'cpny_id',
            4 => 'created_by',
            5 => 'department_id',
            6 => 'keperluan',
        ];
        $orderIdx = (int) $request->input('order.0.column', 2);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $columns[$orderIdx] ?? 'doc_date';

        // Base query dari VIEW
        $base = vReceivedList::query();

        // ⬇️ tampilkan HANYA yang belum di-assign
        $base->where(function ($q) {
            $q->whereNull('assignpurchasing')
            ->orWhere('assignpurchasing', '')
            ->orWhere('assignpurchasing', '0'); // jika ada data lama pakai '0'
        });

        if ($doc !== '') {
            $base->where('doc_type', $doc);
        }

        $recordsTotal = (clone $base)->count();

        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('doc_no', 'ilike', "%{$search}%")
                ->orWhere('doc_type', 'ilike', "%{$search}%")
                ->orWhere('created_by', 'ilike', "%{$search}%")
                ->orWhere('assignpurchasing', 'ilike', "%{$search}%")
                ->orWhere('keperluan', 'ilike', "%{$search}%")
                ->orWhereRaw("CAST(cpny_id AS TEXT) ILIKE ?", ["%{$search}%"])
                ->orWhereRaw("CAST(department_id AS TEXT) ILIKE ?", ["%{$search}%"])
                ->orWhereRaw("TO_CHAR(doc_date, 'YYYY-MM-DD HH24:MI:SS') ILIKE ?", ["%{$search}%"]);
            });
        }

        $recordsFiltered = (clone $base)->count();

        $data = $base->select(
                    'doc_type',
                    'src_id',
                    'doc_no',
                    'assignpurchasing',
                    'doc_date',
                    'cpny_id',
                    'created_by',
                    'department_id',
                    'keperluan',
                    'row_id'
                )
                ->orderBy($orderCol, $orderDir)
                ->orderBy('doc_no', 'desc')
                ->skip($start)
                ->take($length)
                ->get();

        // ⬇️ normalkan '0' menjadi null agar dropdown tampil kosong rapi
        $data->transform(function ($row) {
            if ($row->assignpurchasing === '0') {
                $row->assignpurchasing = null;
            }
            return $row;
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }


    public function ReceivedListUsers(Request $request)
{
    $q = trim((string) $request->query('q', ''));
    $qLower = mb_strtolower($q);

    $rows = User::query()
        ->where('departmentid','PURCHASING') // kalau mau filter
        ->when($q !== '', function ($qq) use ($qLower) {
            $qq->where(function ($w) use ($qLower) {
                $w->whereRaw('LOWER(name) LIKE ?', ["%{$qLower}%"])
                  ->orWhereRaw('LOWER(username) LIKE ?', ["%{$qLower}%"]);
            });
        })
        ->orderBy('name')
        ->limit(50)
        ->get(['username', 'name']); // <-- TANPA alias 'as id'

    // Map ke format Select2, paksa id = string
    $results = $rows->map(fn ($r) => [
        'id'   => (string) $r->username,               // username SEBAGAI id
        'text' => $r->name ?: (string) $r->username,   // label yang tampil
    ])->values();

    return response()->json(['results' => $results]);
}


    public function ReceivedListUsers_xxx(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $qLower = mb_strtolower($q);

        $users = User::query()
            ->where('departmentid','PURCHASING')
            ->when($q !== '', function ($qq) use ($qLower) {
                $qq->where(function ($w) use ($qLower) {
                    $w->whereRaw('LOWER(name) LIKE ?', ["%{$qLower}%"])
                    ->orWhereRaw('LOWER(username) LIKE ?', ["%{$qLower}%"]);
                });
            })
            ->orderBy('name')
            ->limit(50)
            ->get(['username as id', 'name as text']);

        return response()->json(['results' => $users]);
    }



    public function AssignPurchasing(Request $request)
    {
        $data = $request->validate([
            'items' => ['required','array','min:1'],
            'items.*.doc_type' => 'required|in:SPPB,SPPJ,SPPK,SPPT',
            'items.*.src_id'   => ['required','integer','min:1'],
            'items.*.assignpurchasing' => ['nullable','string','max:100'], // ⬅️ nullable
        ]);

        Log::info('AssignPurchasing payload', ['raw_items' => $data['items']]);

        $username = Auth::user()->username ?? 'system';
        $now      = Carbon::now('Asia/Jakarta');

        // hanya kirim yang benar-benar terisi (bukan '', bukan '0')
       // ambil hanya yang benar-benar diisi
        $items = collect($data['items'])
            ->filter(fn($it) =>
                isset($it['assignpurchasing']) &&
                $it['assignpurchasing'] !== '' &&
                $it['assignpurchasing'] !== '0'   // ⬅️ guard terakhir
            )
            ->values();

       
        if ($items->isEmpty()) {
            return response()->json([
                'success' => true,
                'updated' => 0,
                'skipped' => count($data['items']),
                'message' => 'Tidak ada perubahan untuk disimpan.',
            ]);
        }

        $map = [
            'SPPB' => 'tr_sppb',
            'SPPJ' => 'tr_sppj',
            'SPPK' => 'tr_sppk',
            'SPPT' => 'tr_sppt',
        ];

        DB::connection('pgsql')->transaction(function () use ($items, $map, $username, $now) {
            $pg = DB::connection('pgsql'); // <-- pakai koneksi ini
            foreach ($items as $it) {
                $pg->table($map[$it['doc_type']])
                ->where('id', $it['src_id'])
                ->update([
                    'assignpurchasing' => $it['assignpurchasing'],
                    'assigndate'       => $now,
                    'assignby'         => $username,
                    'updated_by'       => $username,
                ]);
            }
        });


        return response()->json([
            'success' => true,
            'updated' => $items->count(),
            'skipped' => 0,
            'message' => 'Assign Purchasing updated.',
        ]);
    }








}
