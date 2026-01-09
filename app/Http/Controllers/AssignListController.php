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
use App\Models\MsLocation;
use App\Models\MsSubLocation;
use App\Models\vAssignList;
use App\Models\vSppbjktOnProgress;
use App\Models\vCsJobs;
use App\Models\vCsRevision;
use Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Vinkla\Hashids\Facades\Hashids;
use App\Models\vTransferPurch;


class AssignListController extends Controller
{

    public function AssignList()
    {
        return view('pages.canvass.assignlist');
    }


    public function AssignList_xxx()
    {
        // Assign List (unassigned)
        $base = vAssignList::query()->whereNull('assignpurchasing');

        $all  = (clone $base)->count();
        $sppb = (clone $base)->where('doc_type', 'SPPB')->count();
        $sppj = (clone $base)->where('doc_type', 'SPPJ')->count();
        $sppk = (clone $base)->where('doc_type', 'SPPK')->count();
        $sppt = (clone $base)->where('doc_type', 'SPPT')->count();

        // Transfer Jobs (assigned & open)
        $tbase = vTransferPurch::query()
            ->where('assignpurchasing', '<>', '')
            ->where('totalopenordered', '<>', 0);

        $t_all  = (clone $tbase)->count();
        $t_sppb = (clone $tbase)->where('doc_type', 'SPPB')->count();
        $t_sppj = (clone $tbase)->where('doc_type', 'SPPJ')->count();
        $t_sppk = (clone $tbase)->where('doc_type', 'SPPK')->count();
        $t_sppt = (clone $tbase)->where('doc_type', 'SPPT')->count();

        return view('pages.canvass.assignlist', compact(
            'all','sppb','sppj','sppk','sppt',
            't_all','t_sppb','t_sppj','t_sppk','t_sppt'
        ));
    }

    
    public function AssignListJson(Request $request)
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
         $base = vAssignList::query()
            ->with('creator:username,name') // eager load nama user
            ->whereNull('assignpurchasing'); 

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
            $row->eid = Hashids::encode($row->src_id);
            return $row;
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    public function AssignListUsers(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $rows = User::query()
            ->where('department_id', 'PURCHASING') // filter purchaser saja
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('name', 'ILIKE', "%{$q}%")
                    ->orWhere('username', 'ILIKE', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->limit(50)
            ->get(['username', 'name']);

        // Map ke format Select2, paksa id = string
        $results = $rows->map(fn ($r) => [
            'id'   => (string) $r->username,             // username SEBAGAI id
            'text' => $r->name ?: (string) $r->username, // label yang tampil
        ])->values();

        return response()->json(['results' => $results]);
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

    public function TransferJobsJson(Request $request)
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

        $base = vTransferPurch::query()
            ->with('creator:username,name')
            ->where('assignpurchasing', '<>', '')
            ->where('totalopenordered', '<>', 0);

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

        $data->transform(function ($row) {
            $row->eid = Hashids::encode($row->src_id);
            return $row;
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    public function TransferJobsUpdate(Request $request)
    {
        $data = $request->validate([
            'items' => ['required','array','min:1'],
            'items.*.doc_type' => 'required|in:SPPB,SPPJ,SPPK,SPPT',
            'items.*.src_id'   => ['required','integer','min:1'],
            'items.*.assignpurchasing_new' => ['required','string','max:100'],
        ]);

        $username = Auth::user()->username ?? 'system';
        $now      = Carbon::now('Asia/Jakarta');

        $items = collect($data['items'])
            ->filter(fn($it) => ($it['assignpurchasing_new'] ?? '') !== '' && ($it['assignpurchasing_new'] ?? '') !== '0')
            ->values();

        if ($items->isEmpty()) {
            return response()->json([
                'success' => true,
                'updated' => 0,
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
            $pg = DB::connection('pgsql');
            foreach ($items as $it) {
                $pg->table($map[$it['doc_type']])
                    ->where('id', $it['src_id'])
                    ->update([
                        'assignpurchasing' => $it['assignpurchasing_new'],
                        'assigndate'       => $now,
                        'assignby'         => $username,
                        'updated_by'       => $username,
                    ]);
            }
        });

        return response()->json([
            'success' => true,
            'updated' => $items->count(),
            'message' => 'Transfer Jobs berhasil diupdate.',
        ]);
    }



    

}
