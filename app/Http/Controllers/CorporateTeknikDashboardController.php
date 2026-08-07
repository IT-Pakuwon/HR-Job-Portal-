<?php

namespace App\Http\Controllers;

use App\Models\Autonbr;
use App\Models\TrTicket;
use App\Models\TrTicketActivity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;

class CorporateTeknikDashboardController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Corporate Teknik Dashboard
    |--------------------------------------------------------------------------
    | Ticket / Berita Acara reuse the tr_ticket table from the Eng Ticket
    | module (see EngTicketController) — scoped here purely by role_id
    | (sys_user_role) rather than department membership, per the dashboard's
    | own access rules.
    */

    protected const ENG_TICKET_TYPE = 'ENGSUPPORTTICKET';

    protected const BSFO_TICKET_TYPE = 'BSFOSUPPORTTICKET';

    protected const BA_ENG_TYPE = 'BA_ENG';

    protected const BA_BSFO_TYPE = 'BA_BS';

    protected const ENG_ROLE_ID = 'OPRTEKNIKENG';

    protected const BSFO_ROLE_ID = 'OPRTEKNIKBS';

    protected const MGR_ROLE_ID = 'MGROPRTEKNIKACCESS';

    protected ApprovalDashboardController $approvalController;

    public function __construct(ApprovalDashboardController $approvalController)
    {
        $this->approvalController = $approvalController;
    }

    protected function hasRole(string $roleId): bool
    {
        return (bool) auth()->user()?->hasRole($roleId);
    }

    protected function ticketTypesForUser(): array
    {
        if ($this->hasRole(self::MGR_ROLE_ID)) {
            return [self::ENG_TICKET_TYPE, self::BSFO_TICKET_TYPE];
        }

        $types = [];

        if ($this->hasRole(self::ENG_ROLE_ID)) {
            $types[] = self::ENG_TICKET_TYPE;
        }

        if ($this->hasRole(self::BSFO_ROLE_ID)) {
            $types[] = self::BSFO_TICKET_TYPE;
        }

        return $types;
    }

    protected function baTypesForUser(): array
    {
        if ($this->hasRole(self::MGR_ROLE_ID)) {
            return [self::BA_ENG_TYPE, self::BA_BSFO_TYPE];
        }

        $types = [];

        if ($this->hasRole(self::ENG_ROLE_ID)) {
            $types[] = self::BA_ENG_TYPE;
        }

        if ($this->hasRole(self::BSFO_ROLE_ID)) {
            $types[] = self::BA_BSFO_TYPE;
        }

        return $types;
    }

    public function summaryJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $ticketTypes = $this->ticketTypesForUser();
        $baTypes = $this->baTypesForUser();

        $openTicket = TrTicket::query()
            ->whereNull('deleted_at')
            ->whereIn('ticket_type', $ticketTypes)
            ->whereNotIn(DB::raw('UPPER(status_pekerjaan)'), ['CANCEL', 'COMPLETED'])
            ->count();

        $openBeritaAcara = TrTicket::query()
            ->whereNull('deleted_at')
            ->whereIn('ticket_type', $baTypes)
            ->whereNotIn(DB::raw('UPPER(status_pekerjaan)'), ['CANCEL', 'COMPLETED'])
            ->count();

        $waitingApproval = collect(
            $this->approvalController->waitingJson($request)->getData(true)['data'] ?? []
        )->count();

        return response()->json([
            'success' => true,
            'data' => [
                'open_ticket' => $openTicket,
                'open_berita_acara' => $openBeritaAcara,
                'waiting_approval' => $waitingApproval,
            ],
        ]);
    }

    protected function baDivision(string $ticketType): string
    {
        return match ($ticketType) {
            self::BA_BSFO_TYPE => 'BS',
            self::BA_ENG_TYPE => 'ENG',
            default => '',
        };
    }

    protected function isBaTicketType(string $ticketType): bool
    {
        return in_array($ticketType, [self::BA_ENG_TYPE, self::BA_BSFO_TYPE], true);
    }

    protected function ticketRows(array $ticketTypes)
    {
        $tickets = TrTicket::query()
            ->select([
                'id', 'ticketid', 'ticketdate', 'cpny_id', 'department_id',
                'ticket_priority', 'ticket_duedate', 'ticket_type',
                'ticket_categoryid', 'ticket_subcategoryid', 'user_peminta',
                'location_id', 'sub_location_id', 'issue_summary', 'pic_ticket', 'status',
            ])
            ->with([
                'category:ticket_categoryid,ticket_category_name',
                'site:siteid,site_name',
                'location:location_id,location_name',
                'subLocation:sub_location_id,sub_location_name',
            ])
            ->whereNull('deleted_at')
            ->whereIn('ticket_type', $ticketTypes)
            ->orderByDesc('ticketdate')
            ->get();

        $activities = TrTicketActivity::query()
            ->select('ticketid', 'status_pekerjaan')
            ->whereIn('ticketid', $tickets->pluck('ticketid'))
            ->orderByDesc('response_date')
            ->get()
            ->unique('ticketid')
            ->keyBy('ticketid');

        return $tickets->map(function ($row) use ($activities) {
            $today = now();

            $isOverdue = $row->ticket_duedate
                ? $today->gt($row->ticket_duedate)
                : false;

            $isBa = $this->isBaTicketType($row->ticket_type);

            $locationName = $isBa
                ? optional($row->location)->location_name
                : optional($row->site)->site_name;

            $subLocationName = $isBa
                ? optional($row->subLocation)->sub_location_name
                : null;

            $baAutoNumber = null;

            if ($isBa) {
                $seq = (int) substr($row->ticketid, -4);
                $dt = $row->ticketdate ? Carbon::parse($row->ticketdate) : now();

                $baAutoNumber = 'BA/'.$this->baDivision($row->ticket_type).'/'.$dt->format('m').'/'.$dt->format('Y').'/'.sprintf('%03d', $seq);
            }

            return [
                'eid' => Hashids::encode($row->id),
                'ticketid' => $row->ticketid,
                'ba_auto_number' => $baAutoNumber,
                'ticketdate' => optional($row->ticketdate)->format('d M Y'),
                'cpny_id' => $row->cpny_id,
                'department_id' => $row->department_id,
                'ticket_priority' => $row->ticket_priority,
                'ticket_duedate' => optional($row->ticket_duedate)->format('d M Y H:i'),
                'ticket_type' => $row->ticket_type,
                'ticket_categoryid' => $row->ticket_categoryid,
                'ticket_category_name' => $row->category->ticket_category_name ?? $row->ticket_categoryid,
                'user_peminta' => $row->user_peminta,
                'location_name' => $locationName,
                'sub_location_name' => $subLocationName,
                'issue_summary' => $row->issue_summary,
                'pic_ticket' => $row->pic_ticket,
                'status_pekerjaan' => $activities[$row->ticketid]->status_pekerjaan ?? '-',
                'status' => $row->status,
                'is_overdue' => $isOverdue,
            ];
        })->values();
    }

    public function ticketJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        return response()->json([
            'success' => true,
            'data' => $this->ticketRows($this->ticketTypesForUser()),
        ]);
    }

    public function beritaAcaraJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        return response()->json([
            'success' => true,
            'data' => $this->ticketRows($this->baTypesForUser()),
        ]);
    }

    public function waitingApprovalJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        return $this->approvalController->waitingJson($request);
    }

    public function approvalHistoryJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        return $this->approvalController->approveJson($request);
    }

    public function approvalDocTypes(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $tab = $request->query('tab', 'approval');

        $data = $tab === 'approval-history'
            ? collect($this->approvalController->approveJson($request)->getData(true)['data'] ?? [])
            : collect($this->approvalController->waitingJson($request)->getData(true)['data'] ?? []);

        $docids = $data
            ->pluck('docid')
            ->map(function ($docid) {
                preg_match('/^[A-Z]+/', $docid, $match);

                return $match[0] ?? null;
            })
            ->filter()
            ->unique()
            ->values();

        $rows = Autonbr::query()
            ->select('doctype', 'doctype_descr')
            ->whereIn('doctype', $docids)
            ->orderBy('doctype')
            ->distinct()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $rows,
        ]);
    }
}
