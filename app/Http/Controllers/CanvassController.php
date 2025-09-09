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
use App\Models\vSppbjktOnProgress;
use App\Models\vCsJobs;
use App\Models\vCsRevision;
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

        return view('pages.canvass.receivedlist', compact('all', 'sppb', 'sppj', 'sppk', 'sppt'));
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
         $base = vReceivedList::query()
            ->with('creator:username,name') // eager load nama user
            ->where(function ($q) {
                $q->whereNull('assignpurchasing')
                ->orWhere('assignpurchasing', '')
                ->orWhere('assignpurchasing', '0');
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


    public function CsJobs()
    {
        // Kartu ringkasan
        $all  = vCsJobs::count();
        $sppb = vCsJobs::where('doc_type', 'SPPB')->count();
        $sppj = vCsJobs::where('doc_type', 'SPPJ')->count();
        $sppk = vCsJobs::where('doc_type', 'SPPK')->count();
        $sppt = vCsJobs::where('doc_type', 'SPPT')->count();

        return view('pages.canvass.csjobs', compact('all', 'sppb', 'sppj', 'sppk', 'sppt'));
    }

    public function CsJobsJson_xxx(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $search = trim((string) $request->input('search.value', ''));
        $doc    = (string) $request->query('doc', ''); // '', 'SPPB','SPPJ','SPPK','SPPT'

        // Urutan kolom DataTables (index -> kolom DB). Kolom 0 = tombol (non-orderable),
        // jadi kita mapping index 1.. dst.
        $columns = [
            0 => 'assigndate',        // tombol Create CS (fallback order)
            1 => 'assigndate',
            2 => 'doc_no',
            3 => 'doc_date',
            4 => 'cpny_id',
            5 => 'created_by',        // "name" diambil dari relasi creator
            6 => 'assignpurchasing',
            7 => 'assignby',
            8 => 'department_id',
            9 => 'keperluan',
        ];

        $orderIdx = (int) $request->input('order.0.column', 3); // default urut doc_date
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $columns[$orderIdx] ?? 'doc_date';

        // Base query dari VIEW
        $base = vCsJobs::query()
            ->with('creator:username,name');

        // (opsional) filter per doc_type
        if ($doc !== '') {
            $base->where('doc_type', $doc);
        }

        // (opsional) jika kamu memang hanya ingin yang SUDAH di-assign (csjobs untuk proses CS):
        // $base->whereNotNull('assignpurchasing')->where('assignpurchasing', '<>', '')->where('assignpurchasing', '<>', '0');

        // (opsional) jika kamu hanya ingin yang BELUM di-assign:
        // $base->where(function ($q) {
        //     $q->whereNull('assignpurchasing')
        //       ->orWhere('assignpurchasing', '')
        //       ->orWhere('assignpurchasing', '0');
        // });

        $recordsTotal = (clone $base)->count();

        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('doc_no', 'ilike', "%{$search}%")
                ->orWhere('doc_type', 'ilike', "%{$search}%")
                ->orWhere('created_by', 'ilike', "%{$search}%")
                ->orWhere('assignpurchasing', 'ilike', "%{$search}%")
                ->orWhere('assignby', 'ilike', "%{$search}%")
                ->orWhere('keperluan', 'ilike', "%{$search}%")
                ->orWhereRaw("CAST(cpny_id AS TEXT) ILIKE ?", ["%{$search}%"])
                ->orWhereRaw("CAST(department_id AS TEXT) ILIKE ?", ["%{$search}%"])
                ->orWhereRaw("TO_CHAR(doc_date, 'YYYY-MM-DD HH24:MI:SS') ILIKE ?", ["%{$search}%"])
                ->orWhereRaw("TO_CHAR(assigndate, 'YYYY-MM-DD HH24:MI:SS') ILIKE ?", ["%{$search}%"]);
            });
        }

        $recordsFiltered = (clone $base)->count();

        $data = $base->select(
                    'row_id',
                    'doc_type',
                    'src_id',
                    'doc_no',
                    'doc_date',
                    'cpny_id',
                    'department_id',
                    'keperluan',
                    'created_by',
                    'assignpurchasing',
                    'assigndate',
                    'assignby'
                )
                ->orderBy($orderCol, $orderDir)
                ->orderBy('doc_no', 'desc')
                ->skip($start)
                ->take($length)
                ->get();

        // Normalisasi + inject "created_by_name"
        $data->transform(function ($row) {
            if ($row->assignpurchasing === '0') {
                $row->assignpurchasing = null;
            }
            $row->created_by_name = optional($row->creator)->name; // dari eager load
            unset($row->creator);
            return $row;
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    private function buildJobsJson($base, Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $search = trim((string) $request->input('search.value', ''));
        $doc    = (string) $request->query('doc', '');

        $columns = [
            0 => 'assigndate',
            1 => 'doc_no',
            2 => 'doc_date',
            3 => 'cpny_id',
            4 => 'created_by',
            5 => 'assignpurchasing',
            6 => 'assignby',
            7 => 'department_id',
            8 => 'keperluan',
        ];
        $orderIdx = (int) $request->input('order.0.column', 2);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $columns[$orderIdx] ?? 'doc_date';

        $base->with('creator:username,name');

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
                ->orWhere('assignby', 'ilike', "%{$search}%")
                ->orWhere('keperluan', 'ilike', "%{$search}%")
                ->orWhereRaw("CAST(cpny_id AS TEXT) ILIKE ?", ["%{$search}%"])
                ->orWhereRaw("CAST(department_id AS TEXT) ILIKE ?", ["%{$search}%"])
                ->orWhereRaw("TO_CHAR(doc_date, 'YYYY-MM-DD HH24:MI:SS') ILIKE ?", ["%{$search}%"])
                ->orWhereRaw("TO_CHAR(assigndate, 'YYYY-MM-DD HH24:MI:SS') ILIKE ?", ["%{$search}%"]);
            });
        }

        $recordsFiltered = (clone $base)->count();

        $data = $base->select(
                    'doc_type',
                    'src_id',
                    'doc_no',
                    'doc_date',
                    'assigndate',
                    'assignby',
                    'assignpurchasing',
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

        // enrich
        $data->transform(function ($row) {
            $row->created_by_name = optional($row->creator)->name;
            // normalisasi optional field biar aman di front-end
            $row->assigndate        = $row->assigndate ?? null;
            $row->assignby          = $row->assignby ?? null;
            $row->assignpurchasing  = $row->assignpurchasing ?? null;
            return $row;
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    /**
     * TAB 1: CS Jobs (punya saya) -> vCsJobs where assignpurchasing = user login
     */
    public function CsJobsMineJson(Request $request)
    {
        $username = Auth::user()->username ?? '';
        $base = vCsJobs::query()->where('assignpurchasing', $username);
        return $this->buildJobsJson($base, $request);
    }

    /**
     * TAB 2: All Jobs -> vCsJobs (tanpa filter assignee)
     */
    public function CsJobsAllJson(Request $request)
    {
        $base = vCsJobs::query();
        return $this->buildJobsJson($base, $request);
    }

    /**
     * TAB 3: My Revision -> vCsRevision
     */
    public function CsJobsRevisionJson(Request $request)
    {
        $base = vCsRevision::query();
        return $this->buildJobsJson($base, $request);
    }

    /**
     * TAB 4: SPPBJKT IN Progress -> vSppbjktOnProgress
     */
    public function SppbjktOnProgressJson(Request $request)
    {
        $base = vSppbjktOnProgress::query();
        return $this->buildJobsJson($base, $request);
    }





}
