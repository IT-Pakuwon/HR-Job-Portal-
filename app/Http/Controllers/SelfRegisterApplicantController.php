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
use App\Models\JobLevel;
use App\Models\JobResponsiblities;
use App\Models\JobQualification;
use App\Models\Usercpny;
use App\Models\Userdept;
use App\Models\User;
use App\Models\Jobposting;
use App\Models\JobpostingResponsiblities;
use App\Models\JobpostingQualification;
use App\Models\AutonbrJobportal;
use Mail;
use Vinkla\Hashids\Facades\Hashids;

class SelfRegisterApplicantController extends Controller
{


    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Your session has expired. Please sign in again.'], 401)
                : redirect()->route('login')->with('error', 'Your session has expired. Please sign in again.');
        }

        $q = DB::connection('mysql3')->table('viewselfregister');

        $all      = (clone $q)->count();       

        return view('pages.selfregister.selfapplicant', compact('all'));
    }

    public function json(Request $request)
    {
        $status = $request->query('status'); // '', 'HP', 'R', 'C'

        $start    = (int) $request->input('start', 0);
        $length   = (int) $request->input('length', 10);
        $global   = trim((string) $request->input('search.value', ''));
        $orderIdx = (int) $request->input('order.0.column', 1);
        $orderDir = strtolower($request->input('order.0.dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $nameToDb = [
            'docid'          => 'vc.docid',
            'apply_date'     => 'vc.apply_date',
            'fullname'       => 'vc.fullname',
            'education_name' => 'vc.education_name',
            'religion'       => 'vc.religion',
            'height'         => 'vc.height',
            'weight'         => 'vc.weight',
            'company_name'   => 'vc.company_name',
            'status'         => 'vc.status',
        ];

        $base = DB::connection('mysql3')->table('viewselfregister as vc');

        // Filter status card
        if (!empty($status)) {
            if ($status === 'HP') $base->whereIn('vc.status', ['H','P']);
            else $base->where('vc.status', $status); // R / C / dll
        }

        $recordsTotal = (clone $base)->count();

        $query = (clone $base);

        // Global search
        if ($global !== '') {
            $like = "%{$global}%";
            $query->where(function ($q) use ($like) {
                $q->where('vc.docid', 'like', $like)
                ->orWhere('vc.fullname', 'like', $like)
                ->orWhere('vc.education_name', 'like', $like)
                ->orWhere('vc.religion', 'like', $like)
                ->orWhere('vc.company_name', 'like', $like)
                ->orWhere('vc.apply_date', 'like', $like)
                ->orWhere('vc.status', 'like', $like);
            });
        }

        // Per-column search
        $cols = $request->input('columns', []);
        foreach ($cols as $c) {
            $name = $c['name'] ?? null;
            $val  = isset($c['search']['value']) ? trim((string) $c['search']['value']) : '';
            if (!$name || $val === '') continue;

            $dbcol = $nameToDb[$name] ?? null;
            if (!$dbcol) continue;

            $query->where($dbcol, 'like', "%{$val}%");
        }

        $recordsFiltered = (clone $query)->count();

        // Sorting
        $orderName = $request->input("columns.$orderIdx.name");
        $orderBy   = $nameToDb[$orderName] ?? 'vc.docid';

        // Paging
        if ($length !== -1) {
            $query->skip($start)->take($length);
        }

        $rows = $query->select([
                'vc.id',
                'vc.docid',
                'vc.apply_date',
                'vc.fullname',
                'vc.education_name',
                'vc.religion',
                'vc.height',
                'vc.weight',
                'vc.company_name',
                'vc.status',
            ])
            ->orderBy($orderBy, $orderDir)
            ->get();

        $data = $rows->map(function ($r) {
            return [
                'eid'            => Hashids::encode($r->id),
                'docid'          => $r->docid,
                'apply_date'     => $r->apply_date,
                'fullname'       => $r->fullname,
                'education_name' => $r->education_name,
                'religion'       => $r->religion,
                'height'         => $r->height,
                'weight'         => $r->weight,
                'company_name'   => $r->company_name,
                'status'         => $r->status,
            ];
        });

        return response()->json([
            'draw'            => (int) $request->input('draw'),
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }



    

    

}
