<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\TrSPB;
use App\Models\TrWO;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Str;
use App\Models\TrIssue;


class IssueListController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $u       = $user->username ?? '';
        $cpny_id = $user->cpny_id ?? '';

        // Count untuk header/kartu ringkasan
        $issuejobs = TrSPB::when($cpny_id, fn($q) => $q->where('cpny_id', $cpny_id))
            ->where('status', 'C')
            ->where('totalspbqty', '<>', 0)
            ->count();

        $onProgress = TrIssue::where('created_by', $u)->where('status', 'P')->count();
        $completed  = TrIssue::where('created_by', $u)->where('status', 'C')->count();
        $all        = TrIssue::when($cpny_id, fn($q) => $q->where('cpny_id', $cpny_id))->count();
        $rejected   = TrIssue::where('created_by', $u)->where('status','R')->count();
        $revise     = TrIssue::where('created_by', $u)->where('status','D')->count();

        // Opsional: returnjobs = status C + issuetype 'issue' (tetap)
        $returnjobs = TrIssue::when($cpny_id, fn($q) => $q->where('cpny_id', $cpny_id))
            ->where('status', 'C')
            ->where('issuetype', 'issue')
            ->count();

        return view('pages.issue.issuelist', compact(
            'issuejobs', 'onProgress', 'completed', 'all', 'returnjobs','rejected','revise'
        ));
    }

    public function json(Request $req)
    {
        $scope   = strtolower((string) $req->query('scope', 'issuejobs'));
        $user    = Auth::user();
        $u       = $user->username ?? '';
        $cpny_id = $user->cpny_id ?? '';

        $draw   = (int) $req->input('draw', 1);
        $start  = (int) $req->input('start', 0);
        $length = (int) $req->input('length', 25);
        $search = trim((string) $req->input('search.value', ''));

        // Map label issuetype
        $typeLabel = [
            'IS' => 'Issue',
            'RI' => 'Return Issue',
        ];

        if ($scope === 'issuejobs') {
            // === TrSPB list untuk issuejobs ===
            $base = TrSPB::when($cpny_id, fn($q) => $q->where('cpny_id', $cpny_id))
                ->where('status', 'C')
                ->where('totalspbqty', '<>', 0)
                ->select([
                    'id', 'spbid', 'spbdate', 'cpny_id', 'keperluan', 'created_by', 'status',
                ]);

            $orderColumns = [
                0 => 'id',
                1 => 'spbid',
                2 => 'spbdate',
                3 => 'cpny_id',
                4 => 'keperluan',
                5 => 'created_by',
            ];

            if ($search !== '') {
                $base->where(function ($q) use ($search) {
                    $q->where('spbid', 'ilike', "%{$search}%")
                    ->orWhere('cpny_id', 'ilike', "%{$search}%")
                    ->orWhere('keperluan', 'ilike', "%{$search}%")
                    ->orWhere('created_by', 'ilike', "%{$search}%")
                    ->orWhereRaw("TO_CHAR(spbdate,'YYYY-MM-DD') ILIKE ?", ["%{$search}%"]);
                });
            }
        } else {
            // === TrIssue untuk onprogress/completed/rejected/revise/returnjobs/all ===
            $base = TrIssue::query()
                ->when($cpny_id, fn($q) => $q->where('cpny_id', $cpny_id))
                ->when($scope === 'onprogress', fn($q) => $q->where('created_by', $u)->where('status', 'P'))
                ->when($scope === 'completed',  fn($q) => $q->where('created_by', $u)->where('status', 'C'))
                ->when($scope === 'returnjobs', fn($q) => $q->where('status', 'C')->where('issuetype', 'RI'))
                ->when($scope === 'rejected',   fn($q) => $q->where('created_by', $u)->where('status', 'R'))
                ->when($scope === 'revise',     fn($q) => $q->where('created_by', $u)->where('status', 'D'))
                ->select([
                    'id', 'issueid', 'issuedate', 'issuetype', 'spbid', 'cpny_id', 'created_by', 'status',
                ]);

            $orderColumns = [
                0 => 'id',
                1 => 'issueid',
                2 => 'issuedate',
                3 => 'issuetype',
                4 => 'spbid',
                5 => 'cpny_id',
                6 => 'created_by',
            ];

            if ($search !== '') {
                $base->where(function ($q) use ($search) {
                    // Cari juga berdasarkan label "Issue" / "Return Issue"
                    $q->where('issueid', 'ilike', "%{$search}%")
                    ->orWhere('spbid', 'ilike', "%{$search}%")
                    ->orWhere('issuetype', 'ilike', "%{$search}%")
                    ->orWhereRaw("CASE WHEN issuetype='IS' THEN 'Issue' WHEN issuetype='RI' THEN 'Return Issue' ELSE issuetype END ILIKE ?", ["%{$search}%"])
                    ->orWhere('cpny_id', 'ilike', "%{$search}%")
                    ->orWhere('created_by', 'ilike', "%{$search}%")
                    ->orWhereRaw("TO_CHAR(issuedate,'YYYY-MM-DD') ILIKE ?", ["%{$search}%"]);
                });
            }
        }

        // Hitung total dan filtered
        $recordsTotal    = (clone $base)->count();
        $recordsFiltered = (clone $base)->count();

        // Ordering
        $defaultOrderIdx = 2; // spbdate / issuedate
        $orderIdx = (int) $req->input('order.0.column', $defaultOrderIdx);
        $orderDir = $req->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

        // Kolom order fallback per scope
        if ($scope === 'issuejobs') {
            $orderCol = $orderColumns[$orderIdx] ?? 'spbdate';
        } else {
            $orderCol = $orderColumns[$orderIdx] ?? 'issuedate';
        }

        $rows = $base->orderBy($orderCol, $orderDir)
                    ->orderBy(($scope === 'issuejobs') ? 'spbid' : 'issueid', 'desc')
                    ->skip($start)->take($length)
                    ->get();

        // Transform per scope
        if ($scope === 'issuejobs') {
            $rows->transform(function ($r) {
                $r->spbdate_fmt = $r->spbdate ? \Carbon\Carbon::parse($r->spbdate)->format('Y-m-d') : null;
                $r->spb_eid     = Hashids::encode((string)$r->id);
                return $r;
            });
        } else {
            $rows->transform(function ($r) use ($typeLabel) {
                $r->issuedate_fmt = $r->issuedate ? \Carbon\Carbon::parse($r->issuedate)->format('Y-m-d') : null;
                $r->issue_eid     = Hashids::encode((string)$r->id);
                // Tampilkan label langsung di field issuetype
                $r->issuetype     = $typeLabel[$r->issuetype] ?? $r->issuetype;
                return $r;
            });
        }

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $rows,
        ]);
    }

}
