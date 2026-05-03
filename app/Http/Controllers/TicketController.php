<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\HasAutonbr;
use App\Models\MsLocation;
use App\Models\MsSubLocation;
use App\Models\MsTicketCategory;
use App\Models\MsTicketCategoryDept;
use App\Models\MsTicketPriority;
use App\Models\MsTicketSubcategory;
use App\Models\MsTicketType;
use App\Models\TrAttachment;
use App\Models\TrMessage;
use App\Models\TrTicket;
use App\Models\TrTicketActivity;
use App\Models\User;
use App\Models\Usercpny;
use App\Models\Userdept;
use Carbon\Carbon;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Vinkla\Hashids\Facades\Hashids;

class TicketController extends Controller
{
    use HasAutonbr;

    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $cpnyIds = Usercpny::where('username', $user->username)
            ->pluck('cpny_id')
            ->toArray();

        $deptIds = Userdept::where('username', $user->username)
            ->pluck('department_id')
            ->toArray();

        $q = TrTicket::query()
            ->whereIn('cpny_id', $cpnyIds)
            ->whereIn('department_id', $deptIds);

        $all = (clone $q)->count();
        $waiting = (clone $q)->where('status', 'W')->count();
        $progress = (clone $q)->where('status', 'P')->count();
        $completed = (clone $q)->where('status', 'C')->count();
        $reopen = (clone $q)->where('status', 'R')->count();

        return view('pages.ticket.ticket', compact(
            'all',
            'waiting',
            'progress',
            'completed',
            'reopen'
        ));
    }

    public function json(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $cpnyIds = Usercpny::where('username', $user->username)
            ->pluck('cpny_id')
            ->toArray();

        $deptIds = Userdept::where('username', $user->username)
            ->pluck('department_id')
            ->toArray();

        $status = $request->query('status');
        $search = trim((string) $request->query('search'));

        $query = TrTicket::query()
            ->whereIn('cpny_id', $cpnyIds)
            ->whereIn('department_id', $deptIds);

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('ticketid', 'ilike', "%{$search}%")
                    ->orWhere('ticket_type', 'ilike', "%{$search}%")
                    ->orWhere('ticket_categoryid', 'ilike', "%{$search}%")
                    ->orWhere('ticket_subcategoryid', 'ilike', "%{$search}%")
                    ->orWhere('ticket_priority', 'ilike', "%{$search}%")
                    ->orWhere('issue_summary', 'ilike', "%{$search}%")
                    ->orWhere('pic_ticket', 'ilike', "%{$search}%")
                    ->orWhere('created_by', 'ilike', "%{$search}%");
            });
        }

        $rows = $query
            ->orderByDesc('ticketdate')
            ->orderByDesc('ticketid')
            ->get([
                'id',
                'ticketid',
                'ticketdate',
                'ticket_type',
                'ticket_categoryid',
                'ticket_subcategoryid',
                'ticket_priority',
                'ticket_sla_days',
                'ticket_duedate',
                'issue_summary',
                'issue_descr',
                'location_id',
                'sub_location_id',
                'status',
                'status_pekerjaan',
                'pic_ticket',
                'created_by',
            ]);

        $rows->transform(function ($row) {
            $row->eid = Hashids::encode($row->id);

            unset($row->id);

            return $row;
        });

        return response()->json([
            'data' => $rows,
        ]);
    }

    public function create()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $usercpny = Usercpny::where('username', $user->username)->get();
        $usercpny2 = Usercpny::where('username', $user->username)->first();

        $userdept = Userdept::where('username', $user->username)->get();
        $userdept2 = Userdept::where('username', $user->username)->first();

        $types = MsTicketType::where('status', 'A')
            ->orderBy('ticket_type_name')
            ->get();

        $locations = MsLocation::where('status', 'A')
            ->orderBy('location_name')
            ->get();

        return view('pages.ticket.create', compact(
            'usercpny',
            'usercpny2',
            'userdept',
            'userdept2',
            'types',
            'locations'
        ));
    }

    public function categoryByType(Request $request)
    {
        $rows = MsTicketCategory::query()
            ->where('ticket_type', $request->ticket_type)
            ->where('status', 'A')
            ->orderBy('ticket_category_name')
            ->get();

        return response()->json($rows);
    }

    public function subcategoryByCategory(Request $request)
    {
        $rows = MsTicketSubcategory::query()
            ->where('ticket_type', $request->ticket_type)
            ->where('ticket_categoryid', $request->ticket_categoryid)
            ->where('status', 'A')
            ->orderBy('ticket_subcategory_name')
            ->get();

        return response()->json($rows);
    }

    public function priorityByCategory(Request $request)
    {
        $rows = MsTicketPriority::query()
            ->where('ticket_type', $request->ticket_type)
            ->where('ticket_categoryid', $request->ticket_categoryid)
            ->where('status', 'A')
            ->orderBy('ticket_priority_name')
            ->get();

        return response()->json($rows);
    }

    public function subLocation(Request $request)
    {
        $rows = MsSubLocation::query()
            ->where('location_id', $request->location_id)
            ->where('status', 'A')
            ->orderBy('sub_location_name')
            ->get();

        return response()->json($rows);
    }

    public function store(Request $request)
    {
        $request->validate([
            'cpny_id' => 'required|string',
            'department_id' => 'required|string',
            'ticket_type' => 'required|string',
            'ticket_categoryid' => 'required|string',
            'ticket_subcategoryid' => 'required|string',
            'ticket_priority' => 'required|string',
            'location_id' => 'required|string',
            'sub_location_id' => 'nullable|string',
            'issue_summary' => 'required|string|max:255',
            'issue_descr' => 'required|string|min:5',
            'attachments' => 'required|array|min:1',
            'attachments.*' => 'required|file|mimes:jpg,jpeg,png,pdf,xlsx,xls,doc,docx|max:5120',
        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {

            $user = $request->user();

            $username = $user->username ?? 'system';

            $dt = now();

            $priority = MsTicketPriority::query()
                ->where('ticket_type', $request->ticket_type)
                ->where('ticket_categoryid', $request->ticket_categoryid)
                ->where('ticket_priority', $request->ticket_priority)
                ->first();

            if (!$priority) {
                throw new \RuntimeException('Priority setup not found');
            }

            $slaDays = (int) $priority->ticket_sla_days;

            $dueDate = Carbon::parse($dt)->addDays($slaDays);

            $pic = MsTicketCategoryDept::query()
                ->where('ticket_type', $request->ticket_type)
                ->where('ticket_categoryid', $request->ticket_categoryid)
                ->where('department_id', $request->department_id)
                ->where('status', 'A')
                ->orderBy('username')
                ->first();

            if (!$pic) {
                throw new \RuntimeException('PIC ticket department not found');
            }

            $auto = $this->nextAutonbr(
                'TIC',
                $dt->year,
                str_pad($dt->month, 2, '0', STR_PAD_LEFT),
                $username,
                'Ticketing'
            );

            $ticketid =
                'TIC'.
                substr((string) $dt->year, 2).
                str_pad($dt->month, 2, '0', STR_PAD_LEFT).
                sprintf('%04d', (int) $auto['next']);

            $ticket = new TrTicket();

            $ticket->ticketid = $ticketid;
            $ticket->ticketdate = $dt->toDateString();
            $ticket->cpny_id = $request->cpny_id;
            $ticket->department_id = $request->department_id;
            $ticket->ticket_priority = $request->ticket_priority;
            $ticket->ticket_sla_days = $slaDays;
            $ticket->ticket_duedate = $dueDate;
            $ticket->ticket_type = $request->ticket_type;
            $ticket->ticket_categoryid = $request->ticket_categoryid;
            $ticket->ticket_subcategoryid = $request->ticket_subcategoryid;
            $ticket->user_peminta = $username;
            $ticket->location_id = $request->location_id;
            $ticket->sub_location_id = $request->sub_location_id;
            $ticket->issue_summary = $request->issue_summary;
            $ticket->issue_descr = $request->issue_descr;
            $ticket->status = 'W';
            $ticket->pic_ticket = null;
            $ticket->created_by = $username;

            $ticket->save();

            DB::connection('pgsql5')->commit();

            // =========================
            // AFTER COMMIT
            // =========================

            if ($request->hasFile('attachments')) {

                $meta = [
                    'refnbr' => $ticketid,
                    'doctype' => 'TIC',
                    'cpnyid' => $request->cpny_id,
                    'departementid' => $request->department_id,
                    'base_folder' => 'att-ticket',
                    'created_by' => $username,
                ];

                $files = $request->file('attachments');

                if (!is_array($files)) {
                    $files = [$files];
                }

                app(TrAttachmentController::class)
                    ->uploadInternal($meta, $files);
            }

            $this->notifyTicketCreated($ticket);

            return response()->json([
                'message' => 'Ticket created successfully',
                'ticketid' => $ticketid,
                'eid' => Hashids::encode($ticket->id),
            ]);

        } catch (\Throwable $e) {

            DB::connection('pgsql5')->rollBack();

            dd([
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    public function detail($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;

        $ticket = TrTicket::where('id', $id)->firstOrFail();

        abort_if(!$id, 404);

        $activities = TrTicketActivity::query()
            ->where('ticketid', $ticket->ticketid)
            ->orderBy('response_date', 'asc')
            ->get();

        $rows = TrAttachment::where('refnbr', $ticket->ticketid)
            ->where('doctype', 'TIC')
            ->where('status', 'A')
            ->orderBy('created_at', 'desc')
            ->get();

        $attachments = collect();

        if ($rows->count()) {
            $config = config('filesystems.disks.gcs');

            $keyFilePath = $config['key_file'];

            if (!Str::startsWith($keyFilePath, ['/', 'C:\\', 'D:\\'])) {
                $keyFilePath = base_path($keyFilePath);
            }

            $storage = new StorageClient([
                'projectId' => $config['project_id'],
                'keyFilePath' => $keyFilePath,
            ]);

            $bucket = $storage->bucket($config['bucket']);

            $attachments = $rows->map(function ($r) use ($bucket) {
                $objectPath = rtrim($r->folder, '/').'/'.$r->filename;

                $object = $bucket->object($objectPath);

                $signedUrl = null;

                try {
                    $signedUrl = $object->signedUrl(
                        new \DateTimeImmutable('+10 minutes'),
                        ['version' => 'v4']
                    );
                } catch (\Throwable $e) {
                    \Log::warning('Signed URL gagal', [
                        'path' => $objectPath,
                        'error' => $e->getMessage(),
                    ]);
                }

                return (object) [
                    'id' => $r->id,
                    'display_name' => $r->attachment_name,
                    'created_by' => $r->created_by,
                    'created_at' => $r->created_at,
                    'url' => $signedUrl,
                    'folder' => $r->folder,
                    'filename' => $r->filename,
                    'extention' => $r->extention,
                    'size' => $r->filesize,
                ];
            });
        }

        return response()->json([
            'ticket' => $ticket,
            'activities' => $activities,
            'attachments' => $attachments,
        ]);
    }

    public function update(Request $request, $hash)
    {
        $request->validate([
            'ticket_priority' => 'required|string',
            'issue_summary' => 'required|string|max:255',
            'issue_descr' => 'required|string|min:5',
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,xlsx,xls,doc,docx|max:5120',
        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {
            $user = $request->user();

            $username = $user->username ?? 'system';

            $id = Hashids::decode($hash)[0] ?? null;

            abort_if(!$id, 404);

            $ticket = TrTicket::lockForUpdate()->findOrFail($id);

            if ($ticket->created_by !== $username) {
                return response()->json([
                    'message' => 'Only creator can edit ticket',
                ], 403);
            }

            if (!in_array($ticket->status, ['W', 'R'])) {
                return response()->json([
                    'message' => 'Ticket cannot be edited',
                ], 422);
            }

            $priority = MsTicketPriority::query()
                ->where('ticket_type', $ticket->ticket_type)
                ->where('ticket_categoryid', $ticket->ticket_categoryid)
                ->where('ticket_priority', $request->ticket_priority)
                ->first();

            if (!$priority) {
                throw new \RuntimeException('Priority setup not found');
            }

            $ticket->ticket_priority = $request->ticket_priority;
            $ticket->ticket_sla_days = $priority->ticket_sla_days;
            $ticket->ticket_duedate = now()->addDays((int) $priority->ticket_sla_days);
            $ticket->issue_summary = $request->issue_summary;
            $ticket->issue_descr = $request->issue_descr;
            $ticket->updated_by = $username;
            $ticket->save();

            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr' => $ticket->ticketid,
                    'doctype' => 'TIC',
                    'cpnyid' => $ticket->cpny_id,
                    'departementid' => $ticket->department_id,
                    'base_folder' => 'att-ticket',
                    'created_by' => $username,
                ];
                $files = $request->file('attachments');

                if (!is_array($files)) {
                    $files = [$files];
                }

                app(TrAttachmentController::class)
                    ->uploadInternal($meta, $files);
            }

            DB::connection('pgsql5')->commit();

            return response()->json([
                'message' => 'Ticket updated successfully',
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            report($e);

            return response()->json([
                'message' => 'Failed update ticket',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }

    public function cancel($hash)
    {
        DB::connection('pgsql5')->beginTransaction();

        try {
            $user = Auth::user();

            $username = $user->username ?? 'system';

            $id = Hashids::decode($hash)[0] ?? null;

            abort_if(!$id, 404);

            $ticket = TrTicket::lockForUpdate()->findOrFail($id);

            if ($ticket->created_by !== $username) {
                return response()->json([
                    'message' => 'Only creator can cancel ticket',
                ], 403);
            }

            if (in_array($ticket->status, ['C', 'X'])) {
                return response()->json([
                    'message' => 'Ticket already completed/cancelled',
                ], 422);
            }

            $ticket->status = 'X';
            $ticket->updated_by = $username;
            $ticket->save();

            $this->notifyTicketCancelled($ticket);

            DB::connection('pgsql5')->commit();

            return response()->json([
                'message' => 'Ticket cancelled successfully',
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            report($e);

            return response()->json([
                'message' => 'Failed cancel ticket',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }

    public function startWork(Request $request, $hash)
    {
        DB::connection('pgsql5')->beginTransaction();

        try {

            $user = $request->user();

            $username = $user->username ?? 'system';

            $id = Hashids::decode($hash)[0] ?? null;

            abort_if(!$id, 404);

            $ticket = TrTicket::lockForUpdate()->findOrFail($id);

            // =========================
            // VALIDATE PIC MEMBER
            // =========================

            $picDept = MsTicketCategoryDept::query()
                ->where('ticket_type', $ticket->ticket_type)
                ->where('ticket_categoryid', $ticket->ticket_categoryid)
                ->where('department_id', $ticket->department_id)
                ->where('status', 'A')
                ->first();

            if (!$picDept) {
                return response()->json([
                    'message' => 'PIC department setup not found',
                ], 422);
            }

            $allowedPics = collect(
                explode(',', $picDept->username ?? '')
            )
            ->map(fn ($x) => trim($x))
            ->filter()
            ->unique()
            ->values()
            ->toArray();

            if (!in_array($username, $allowedPics)) {
                return response()->json([
                    'message' => 'Only PIC can start ticket',
                ], 403);
            }

            // =========================
            // PREVENT START IF ALREADY ASSIGNED
            // =========================

            if (
                !empty($ticket->pic_ticket) &&
                $ticket->pic_ticket !== $username
            ) {
                return response()->json([
                    'message' => 'Ticket already assigned to another PIC',
                ], 422);
            }

            // =========================
            // PREVENT DOUBLE START
            // =========================

            if (
                $ticket->status === 'P' &&
                $ticket->status_pekerjaan === 'START'
            ) {
                return response()->json([
                    'message' => 'Ticket already started',
                ], 422);
            }

            // =========================
            // PREVENT START AFTER COMPLETE
            // =========================

            if (in_array($ticket->status, ['C', 'X'])) {
                return response()->json([
                    'message' => 'Completed/cancelled ticket cannot be started',
                ], 422);
            }

            // =========================
            // INSERT ACTIVITY
            // =========================

            $activity = new TrTicketActivity();

            $activity->ticketid = $ticket->ticketid;
            $activity->cpny_id = $ticket->cpny_id;
            $activity->department_id = $ticket->department_id;
            $activity->pic_ticket = $username;
            $activity->response_date = now();
            $activity->response_summary = 'Ticket Started';
            $activity->response_descr = $request->response_descr;
            $activity->status_pekerjaan = 'START';
            $activity->status = 'A';
            $activity->created_by = $username;

            $activity->save();

            // =========================
            // UPDATE HEADER
            // =========================

            $ticket->pic_ticket = $username;
            $ticket->status = 'P';
            $ticket->status_pekerjaan = 'START';
            $ticket->updated_by = $username;

            $ticket->save();

            $this->notifyTicketStarted($ticket);

            DB::connection('pgsql5')->commit();

            return response()->json([
                'message' => 'Ticket started successfully',
            ]);

        } catch (\Throwable $e) {

            DB::connection('pgsql5')->rollBack();

            report($e);

            return response()->json([
                'message' => 'Failed start ticket',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }

    public function progress(Request $request, $hash)
    {
        $request->validate([
            'response_summary' => 'required|string|max:255',
            'response_descr' => 'required|string|min:3',
            'status_pekerjaan' => 'required|in:START,PENDING,COMPLETED',
        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {
            $user = $request->user();

            $username = $user->username ?? 'system';

            $id = Hashids::decode($hash)[0] ?? null;

            abort_if(!$id, 404);

            $ticket = TrTicket::lockForUpdate()->findOrFail($id);

            if ($ticket->pic_ticket !== $username) {
                return response()->json([
                    'message' => 'Only PIC can update progress',
                ], 403);
            }

            if (in_array($ticket->status, ['C', 'X'])) {
                return response()->json([
                    'message' => 'Completed/cancelled ticket cannot be updated',
                ], 422);
            }

            $activity = new TrTicketActivity();
            $activity->ticketid = $ticket->ticketid;
            $activity->cpny_id = $ticket->cpny_id;
            $activity->department_id = $ticket->department_id;
            $activity->pic_ticket = $username;
            $activity->response_date = now();
            $activity->response_summary = $request->response_summary;
            $activity->response_descr = $request->response_descr;
            $activity->status_pekerjaan = $request->status_pekerjaan;
            $activity->status = 'A';
            $activity->created_by = $username;
            $activity->save();

            $ticket->status_pekerjaan = $request->status_pekerjaan;
            $ticket->updated_by = $username;

            if ($request->status_pekerjaan === 'COMPLETED') {
                $ticket->status = 'C';
                $ticket->solution_descr = $request->response_descr;
                $ticket->completed_by = $username;
                $ticket->completed_at = now();
                $ticket->pic_completed_ticket = $username;
            } else {
                $ticket->status = 'P';
            }

            $ticket->save();

            if ($request->status_pekerjaan === 'COMPLETED') {
                $this->notifyTicketCompleted($ticket, $activity);
            } else {
                $this->notifyTicketProgress($ticket, $activity);
            }

            DB::connection('pgsql5')->commit();

            return response()->json([
                'message' => 'Progress updated successfully',
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            report($e);

            return response()->json([
                'message' => 'Failed update progress',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }

    public function reopen(Request $request, $hash)
    {
        $request->validate([
            'reopen_descr' => 'required|string|min:5',
        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {

            $user = $request->user();

            $username = $user->username ?? 'system';

            $id = Hashids::decode($hash)[0] ?? null;

            abort_if(!$id, 404);

            $ticket = TrTicket::lockForUpdate()->findOrFail($id);

            // ONLY CREATOR CAN REOPEN
            if ($ticket->created_by !== $username) {
                return response()->json([
                    'message' => 'Only creator can reopen ticket',
                ], 403);
            }

            // ONLY COMPLETED TICKET CAN REOPEN
            if ($ticket->status !== 'C') {
                return response()->json([
                    'message' => 'Only completed ticket can reopen',
                ], 422);
            }

            // =========================
            // INSERT ACTIVITY
            // =========================

            $activity = new TrTicketActivity();

            $activity->ticketid = $ticket->ticketid;
            $activity->cpny_id = $ticket->cpny_id;
            $activity->department_id = $ticket->department_id;
            $activity->pic_ticket = $username;
            $activity->response_date = now();
            $activity->response_summary = 'Reopen Ticket';
            $activity->response_descr = $request->reopen_descr;
            $activity->status_pekerjaan = 'REOPEN';
            $activity->status = 'A';
            $activity->created_by = $username;

            $activity->save();

            // =========================
            // RESET TO UNASSIGNED
            // =========================

            $ticket->status = 'W';
            $ticket->status_pekerjaan = 'REOPEN';
            $ticket->reopen_ticket = true;
            $ticket->reopen_descr = $request->reopen_descr;

            // IMPORTANT
            $ticket->pic_ticket = null;

            $ticket->updated_by = $username;

            // RESET COMPLETION
            $ticket->completed_by = null;
            $ticket->completed_at = null;
            $ticket->pic_completed_ticket = null;

            $ticket->save();

            // NOTIFY ALL PIC AGAIN
            $this->notifyTicketCreated($ticket);

            DB::connection('pgsql5')->commit();

            return response()->json([
                'message' => 'Ticket reopened successfully',
            ]);

        } catch (\Throwable $e) {

            DB::connection('pgsql5')->rollBack();

            report($e);

            return response()->json([
                'message' => 'Failed reopen ticket',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }

    public function tracking($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);

        $ticket = TrTicket::findOrFail($id);

        $activities = TrTicketActivity::query()
            ->where('ticketid', $ticket->ticketid)
            ->orderBy('response_date')
            ->get();

        return response()->json([
            'ticketid' => $ticket->ticketid,
            'status' => $ticket->status,
            'status_pekerjaan' => $ticket->status_pekerjaan,
            'activities' => $activities,
        ]);
    }

    private function notifyTicketCreated($ticket)
    {
        try {

            $creator = User::where('username', $ticket->created_by)->first();

            $picDept = MsTicketCategoryDept::query()
                ->where('ticket_type', $ticket->ticket_type)
                ->where('ticket_categoryid', $ticket->ticket_categoryid)
                ->where('department_id', $ticket->department_id)
                ->where('status', 'A')
                ->first();

            $picUsers = collect(
                explode(',', $picDept?->username ?? '')
            )
            ->map(fn ($x) => trim($x))
            ->filter()
            ->unique()
            ->values();

            $url = route('ticket.index', [
                'showticket' => Hashids::encode($ticket->id),
            ]);

            $subject = '[Ticket] New Ticket Created - '.$ticket->ticketid;

            $message = "
                <p>New ticket has been created.</p>

                <table cellpadding='6' cellspacing='0' border='1'>
                    <tr>
                        <td><b>Ticket ID</b></td>
                        <td>{$ticket->ticketid}</td>
                    </tr>
                    <tr>
                        <td><b>Category</b></td>
                        <td>{$ticket->ticket_categoryid}</td>
                    </tr>
                    <tr>
                        <td><b>Priority</b></td>
                        <td>{$ticket->ticket_priority}</td>
                    </tr>
                    <tr>
                        <td><b>Issue</b></td>
                        <td>{$ticket->issue_summary}</td>
                    </tr>
                </table>

                <br>

                <a href='{$url}'>Open Ticket</a>
            ";

            $emails = collect();

            if ($creator && $creator->email) {
                $emails->push($creator->email);
            }

            foreach ($picUsers as $username) {

                $user = User::where('username', $username)->first();

                if (!$user || !$user->email) {
                    continue;
                }

                $emails->push($user->email);
            }

            $emails = $emails->unique();

            foreach ($emails as $email) {

                TrMessage::create([
                    'message_date' => now(),
                    'message_to' => $email,
                    'subject' => $subject,
                    'message' => $message,
                    'status' => 'W',
                    'created_by' => auth()->user()->username ?? 'system',
                ]);
            }

        } catch (\Throwable $e) {

            \Log::error('notifyTicketCreated error', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function notifyTicketStarted($ticket)
    {
        try {
            $creator = User::where('username', $ticket->created_by)->first();

            if (!$creator || !$creator->email) {
                return;
            }

            $url = route('ticket.index', [
                'showticket' => Hashids::encode($ticket->id),
            ]);

            $subject = '[Ticket] Ticket Started - '.$ticket->ticketid;

            $message = "
            <p>Your ticket is now being worked by PIC.</p>

            <table cellpadding='6' cellspacing='0' border='1'>
                <tr>
                    <td><b>Ticket ID</b></td>
                    <td>{$ticket->ticketid}</td>
                </tr>
                <tr>
                    <td><b>Issue</b></td>
                    <td>{$ticket->issue_summary}</td>
                </tr>
                <tr>
                    <td><b>PIC</b></td>
                    <td>{$ticket->pic_ticket}</td>
                </tr>
            </table>

            <br>

            <a href='{$url}'>Open Ticket</a>
        ";

            TrMessage::create([
                'message_date' => now(),
                'message_to' => $creator->email,
                'subject' => $subject,
                'message' => $message,
                'status' => 'W',
                'created_by' => auth()->user()->username ?? 'system',
            ]);
        } catch (\Throwable $e) {
            \Log::error('notifyTicketStarted error', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function notifyTicketProgress($ticket, $activity)
    {
        try {
            $creator = User::where('username', $ticket->created_by)->first();

            if (!$creator || !$creator->email) {
                return;
            }

            $url = route('ticket.index', [
                'showticket' => Hashids::encode($ticket->id),
            ]);

            $subject = '[Ticket] Progress Update - '.$ticket->ticketid;

            $message = "
            <p>Your ticket progress has been updated.</p>

            <table cellpadding='6' cellspacing='0' border='1'>
                <tr>
                    <td><b>Ticket ID</b></td>
                    <td>{$ticket->ticketid}</td>
                </tr>
                <tr>
                    <td><b>Status</b></td>
                    <td>{$activity->status_pekerjaan}</td>
                </tr>
                <tr>
                    <td><b>Summary</b></td>
                    <td>{$activity->response_summary}</td>
                </tr>
                <tr>
                    <td><b>Description</b></td>
                    <td>{$activity->response_descr}</td>
                </tr>
            </table>

            <br>

            <a href='{$url}'>Open Ticket</a>
        ";

            TrMessage::create([
                'message_date' => now(),
                'message_to' => $creator->email,
                'subject' => $subject,
                'message' => $message,
                'status' => 'W',
                'created_by' => auth()->user()->username ?? 'system',
            ]);
        } catch (\Throwable $e) {
            \Log::error('notifyTicketProgress error', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function notifyTicketCompleted($ticket, $activity)
    {
        try {
            $creator = User::where('username', $ticket->created_by)->first();

            if (!$creator || !$creator->email) {
                return;
            }

            $url = route('ticket.index', [
                'showticket' => Hashids::encode($ticket->id),
            ]);

            $subject = '[Ticket] Ticket Completed - '.$ticket->ticketid;

            $message = "
            <p>Your ticket has been completed.</p>

            <table cellpadding='6' cellspacing='0' border='1'>
                <tr>
                    <td><b>Ticket ID</b></td>
                    <td>{$ticket->ticketid}</td>
                </tr>
                <tr>
                    <td><b>Solution</b></td>
                    <td>{$activity->response_descr}</td>
                </tr>
                <tr>
                    <td><b>Completed By</b></td>
                    <td>{$ticket->completed_by}</td>
                </tr>
            </table>

            <br>

            <a href='{$url}'>Open Ticket</a>
        ";

            TrMessage::create([
                'message_date' => now(),
                'message_to' => $creator->email,
                'subject' => $subject,
                'message' => $message,
                'status' => 'W',
                'created_by' => auth()->user()->username ?? 'system',
            ]);
        } catch (\Throwable $e) {
            \Log::error('notifyTicketCompleted error', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function notifyTicketReopened($ticket)
    {
        try {
            $creator = User::where('username', $ticket->created_by)->first();
            $pic = User::where('username', $ticket->pic_ticket)->first();

            $url = route('ticket.index', [
                'showticket' => Hashids::encode($ticket->id),
            ]);

            $subject = '[Ticket] Ticket Reopened - '.$ticket->ticketid;

            $message = "
            <p>Ticket has been reopened.</p>

            <table cellpadding='6' cellspacing='0' border='1'>
                <tr>
                    <td><b>Ticket ID</b></td>
                    <td>{$ticket->ticketid}</td>
                </tr>
                <tr>
                    <td><b>Reopen Reason</b></td>
                    <td>{$ticket->reopen_descr}</td>
                </tr>
            </table>

            <br>

            <a href='{$url}'>Open Ticket</a>
        ";

            foreach ([$creator, $pic] as $user) {
                if (!$user || !$user->email) {
                    continue;
                }

                TrMessage::create([
                    'message_date' => now(),
                    'message_to' => $user->email,
                    'subject' => $subject,
                    'message' => $message,
                    'status' => 'W',
                    'created_by' => auth()->user()->username ?? 'system',
                ]);
            }
        } catch (\Throwable $e) {
            \Log::error('notifyTicketReopened error', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function notifyTicketCancelled($ticket)
    {
        try {
            $creator = User::where('username', $ticket->created_by)->first();
            $pic = User::where('username', $ticket->pic_ticket)->first();

            $url = route('ticket.index', [
                'showticket' => Hashids::encode($ticket->id),
            ]);

            $subject = '[Ticket] Ticket Cancelled - '.$ticket->ticketid;

            $message = "
            <p>Ticket has been cancelled.</p>

            <table cellpadding='6' cellspacing='0' border='1'>
                <tr>
                    <td><b>Ticket ID</b></td>
                    <td>{$ticket->ticketid}</td>
                </tr>
                <tr>
                    <td><b>Issue</b></td>
                    <td>{$ticket->issue_summary}</td>
                </tr>
            </table>

            <br>

            <a href='{$url}'>Open Ticket</a>
        ";

            foreach ([$creator, $pic] as $user) {
                if (!$user || !$user->email) {
                    continue;
                }

                TrMessage::create([
                    'message_date' => now(),
                    'message_to' => $user->email,
                    'subject' => $subject,
                    'message' => $message,
                    'status' => 'W',
                    'created_by' => auth()->user()->username ?? 'system',
                ]);
            }
        } catch (\Throwable $e) {
            \Log::error('notifyTicketCancelled error', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
