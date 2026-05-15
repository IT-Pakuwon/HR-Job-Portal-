<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\HasAutonbr;
use App\Http\Controllers\TrAttachmentController;
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
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;
use App\Services\TicketNotificationService;

class TicketController extends Controller
{
    use HasAutonbr;

    protected $notificationService;

    public function __construct(
        TicketNotificationService $notificationService
    ) {
        $this->notificationService =
            $notificationService;
    }

    protected array $workflowTransitions = [

        'response' => [
            'CREATED',
            'TRANSFER',
            'REOPEN',
        ],

        'process' => [
            'RESPONSE',
            'PENDING',
            'ENVISION',
        ],

        'pending' => [
            'PROCESS',
        ],

        'envision' => [
            'PENDING',
        ],

        'complete' => [
            'PROCESS',
            'PENDING',
            'ENVISION',
        ],

        'transfer' => [
            'CREATED',
            'RESPONSE',
            'TRANSFER',
            'REOPEN',
        ],

        'reopen' => [
            'COMPLETED',
        ],

    ];

    protected function canTransition(
        string $current,
        string $action
    ): bool {

        return in_array(
            $current,
            $this->workflowTransitions[$action] ?? []
        );
    }

    public function index(Request $request, $eid = null)
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }


        $companies = collect(
            explode(',', $user->cpny_id)
        )->filter()->map(function ($item) {
            return [
                'cpny_id' => trim($item),
                'cpny_name' => trim($item),
            ];
        })->values();

        $departments = collect(
            explode(',', $user->department_id)
        )->filter()->map(function ($item) {
            return [
                'department_id' => trim($item),
                'department_name' => trim($item),
            ];
        })->values();

        $counts = [
            'all' => TrTicket::count(),

            'created' => TrTicket::where(
                'status_pekerjaan',
                'CREATED'
            )->count(),

            'response' => TrTicket::where(
                'status_pekerjaan',
                'RESPONSE'
            )->count(),

            'process' => TrTicket::where(
                'status_pekerjaan',
                'PROCESS'
            )->count(),

            'pending' => TrTicket::where(
                'status_pekerjaan',
                'PENDING'
            )->count(),

            'envision' => TrTicket::where(
                'status_pekerjaan',
                'ENVISION'
            )->count(),

            'transfer' => TrTicket::where(
                'status_pekerjaan',
                'TRANSFER'
            )->count(),

            'completed' => TrTicket::where(
                'status_pekerjaan',
                'COMPLETED'
            )->count(),

            'reopen' => TrTicket::where(
                'status_pekerjaan',
                'REOPEN'
            )->count(),

            'cancel' => TrTicket::where(
                'status_pekerjaan',
                'CANCEL'
            )->count(),
        ];

        return view('pages.ticket.ticket', [
            'title' => 'Ticket',
            'eid' => $eid,
            'companies' => $companies,
            'departments' => $departments,
            'counts' => $counts,
        ]);
    }

    public function json(Request $request)
    {
        $user = auth()->user();

        $isIT = $this->isITRole();

        $query = TrTicket::with([
            'category',
            'subcategory',
            'priority',
            'location',
            'subLocation',
        ])->whereNull('deleted_at');

        if (!$isIT) {

            $query->where(function ($q) use ($user) {

                $q->where('created_by', $user->username)
                    ->orWhere('pic_ticket', $user->username);
            });
        }

        if ($request->filled('status')) {

            $query->where(
                'status_pekerjaan',
                $request->status
            );
        }

        if ($request->filled('status_filter')) {

            $query->where(
                'status',
                $request->status_filter
            );
        }

        if ($request->filled('status_pekerjaan')) {

            $query->where(
                'status_pekerjaan',
                $request->status_pekerjaan
            );
        }

        if ($request->filled('search')) {

            $search =
                $request->search;

            $query->where(function ($q) use ($search) {

                $q->where(
                    'ticketid',
                    'ilike',
                    "%{$search}%"
                )
                    ->orWhere(
                        'issue_summary',
                        'ilike',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'pic_ticket',
                        'ilike',
                        "%{$search}%"
                    );
            });
        }

        if ($request->filled('ticket_type')) {

            $query->where(
                'ticket_type',
                $request->ticket_type
            );
        }

        if ($request->filled('cpny_id')) {

            $query->where(
                'cpny_id',
                $request->cpny_id
            );
        }

        if ($request->filled('department_id')) {

            $query->where(
                'department_id',
                $request->department_id
            );
        }

        if ($request->filled('date_from')) {

            $query->whereDate(
                'ticketdate',
                '>=',
                $request->date_from
            );
        }

        if ($request->filled('date_to')) {

            $query->whereDate(
                'ticketdate',
                '<=',
                $request->date_to
            );
        }

        return DataTables::of($query)

            ->addColumn('eid', function ($row) {

                return Hashids::encode(
                    $row->id
                );
            })

            ->addColumn('ticket_category', function ($row) {

                return optional(
                    $row->category
                )->ticket_category_name;
            })

            ->addColumn('ticket_subcategory', function ($row) {

                return optional(
                    $row->subcategory
                )->ticket_subcategory_name;
            })

            ->addColumn('priority_name', function ($row) {

                return optional(
                    $row->priority
                )->ticket_priority_name;
            })

            ->addColumn('location_name', function ($row) {

                return optional(
                    $row->location
                )->location_name;
            })

            ->addColumn('sub_location_name', function ($row) {

                return optional(
                    $row->subLocation
                )->sub_location_name;
            })

            ->addColumn('actions', function ($row) {

                return $this->buildActions($row);
            })

            ->rawColumns([
                'actions',
            ])

            ->make(true);
    }
    protected function isITRole()
    {
        return MsTicketCategoryDept::query()
            ->where('username', auth()->user()->username)
            ->where('status', 'A')
            ->exists();
    }

    protected function canAccessTicket($ticket)
    {
        $user = auth()->user();

        return
            $ticket->created_by === $user->username
            || $ticket->pic_ticket === $user->username
            || $this->isITRole();
    }

    public function store(Request $request)
    {
        $doctype = 'TIC';

        $user = $request->user();

        $username = $user->username ?? 'system';

        $dt = Carbon::now();

        $year = (int) $dt->year;

        $month = str_pad($dt->month, 2, '0', STR_PAD_LEFT);

        $request->validate([
            'cpny_id' => 'required',
            'department_id' => 'required',
            'ticket_type' => 'required',
            'ticket_categoryid' => 'required',
            'ticket_subcategoryid' => 'required',
            'location_id' => 'required',
            'sub_location_id' => 'required',
            'issue_summary' => 'required|max:255',
            'issue_descr' => 'required',

            'attachments.*' => [
                'nullable',
                'file',
                'max:5120',
                'mimes:jpg,jpeg,png,pdf,xlsx,xls,doc,docx'
            ],
        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {
            $auto = $this->nextAutonbr(
                $doctype,
                $year,
                $month,
                $username,
                'TIC'
            );

            $urutan = (int) $auto['next'];

            $tglbln = substr((string) $year, 2) . $month;

            $ticketid = $doctype . $tglbln . sprintf('%04d', $urutan);

            $defaultPriority = MsTicketPriority::where(
                'ticket_priority',
                'Medium'
            )->first();

            $ticket = TrTicket::create([
                'ticketid' => $ticketid,
                'ticketdate' => now(),

                'cpny_id' => $request->cpny_id,
                'department_id' => $request->department_id,

                'ticket_type' => $request->ticket_type,
                'ticket_categoryid' => $request->ticket_categoryid,
                'ticket_subcategoryid' => $request->ticket_subcategoryid,

                'ticket_priority' => 'Medium',

                'user_peminta' => $username,

                'location_id' => $request->location_id,
                'sub_location_id' => $request->sub_location_id,

                'issue_summary' => $request->issue_summary,
                'issue_descr' => $request->issue_descr,

                'status' => 'P',
                'status_pekerjaan' => 'CREATED',

                'created_by' => $username,
            ]);

            $this->createActivity([
                'ticketid' => $ticket->ticketid,
                'cpny_id' => $ticket->cpny_id,
                'department_id' => $ticket->department_id,

                'pic_ticket' => $ticket->user_peminta,

                'response_date' => now(),

                'response_summary' => 'Ticket Created',

                'response_descr' => $ticket->issue_descr,

                'status_pekerjaan' => 'CREATED',

                'status' => 'A',

                'created_by' => $username,
            ]);

            $uploadResult = null;

            if ($request->hasFile('attachments')) {

                $meta = [

                    'refnbr' => $ticket->ticketid,

                    'doctype' => 'TIC',

                    'cpny_id' => $ticket->cpny_id,

                    'department_id' => $ticket->department_id,

                    'base_folder' => 'att-ticket/tic',

                    'created_by' => $username,
                ];

                $files = (array) $request->file('attachments');

                try {

                    $uploader = app(
                        TrAttachmentController::class
                    );

                    $uploadResult =
                        $uploader->uploadInternal(
                            $meta,
                            $files
                        );
                } catch (\Throwable $e) {

                    DB::connection('pgsql5')
                        ->rollBack();

                    return response()->json([

                        'success' => false,

                        'message' => 'Failed upload attachment',

                        'error' => config('app.debug')
                            ? $e->getMessage()
                            : null,

                    ], 500);
                }
            }


            DB::connection('pgsql5')->commit();

            $this->notificationService
                ->ticketCreated($ticket);


            return response()->json([
                'success' => true,
                'message' => 'Ticket created successfully.',
            ]);
        } catch (\Throwable $th) {

            DB::connection('pgsql5')->rollBack();

            dd([
                'message' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
                'trace' => $th->getTraceAsString(),
            ]);
        }
    }

    public function update(Request $request, $hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);

        $ticket = TrTicket::findOrFail($id);

        abort_if(
            $ticket->created_by !== auth()->user()->username,
            403
        );

        abort_if(
            $ticket->status !== 'P'
                || $ticket->status_pekerjaan !== 'CREATED',
            403
        );

        $request->validate([
            'ticket_type' => 'required',
            'ticket_categoryid' => 'required',
            'ticket_subcategoryid' => 'required',
            'location_id' => 'required',
            'sub_location_id' => 'required',
            'issue_summary' => 'required|max:255',
            'issue_descr' => 'required',
        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {
            $ticket->update([
                'ticket_type' => $request->ticket_type,
                'ticket_categoryid' => $request->ticket_categoryid,
                'ticket_subcategoryid' => $request->ticket_subcategoryid,
                'location_id' => $request->location_id,
                'sub_location_id' => $request->sub_location_id,
                'issue_summary' => $request->issue_summary,
                'issue_descr' => $request->issue_descr,
                'updated_by' => auth()->user()->username,
            ]);

            $this->createActivity([
                'ticketid' => $ticket->ticketid,
                'cpny_id' => $ticket->cpny_id,
                'department_id' => $ticket->department_id,
                'pic_ticket' => auth()->user()->username,
                'response_date' => now(),
                'response_summary' => 'Ticket Updated',
                'response_descr' => $ticket->issue_descr,
                'status_pekerjaan' => 'CREATED',
                'status' => 'A',
                'created_by' => auth()->user()->username,
            ]);


            $uploadResult = null;

            if ($request->hasFile('attachments')) {

                $meta = [

                    'refnbr' => $ticket->ticketid,

                    'doctype' => 'TIC',

                    'cpny_id' => $ticket->cpny_id,

                    'department_id' => $ticket->department_id,

                    'base_folder' => 'att-ticket/tic',

                    'created_by' => auth()->user()->username,
                ];

                $files = (array) $request->file('attachments');

                try {

                    $uploader = app(
                        TrAttachmentController::class
                    );

                    $uploadResult =
                        $uploader->uploadInternal(
                            $meta,
                            $files
                        );
                } catch (\Throwable $e) {

                    DB::connection('pgsql5')
                        ->rollBack();

                    return response()->json([

                        'success' => false,

                        'message' => 'Failed upload attachment',

                        'error' => config('app.debug')
                            ? $e->getMessage()
                            : null,

                    ], 500);
                }
            }

            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Ticket updated successfully.',
            ]);
        } catch (\Throwable $th) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function cancel($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);

        $ticket = TrTicket::findOrFail($id);

        abort_if(
            !(
                (
                    $ticket->created_by === auth()->user()->username
                    &&
                    $ticket->status === 'P'
                    &&
                    $ticket->status_pekerjaan === 'CREATED'
                )
                ||
                (
                    $this->isITRole()
                    &&
                    $ticket->status_pekerjaan !== 'COMPLETED'
                )
            ),
            403
        );

        DB::connection('pgsql5')->beginTransaction();

        try {
            $ticket->update([
                'status' => 'X',
                'status_pekerjaan' => 'CANCEL',
                'updated_by' => auth()->user()->username,
            ]);

            $this->createActivity([
                'ticketid' => $ticket->ticketid,
                'cpny_id' => $ticket->cpny_id,
                'department_id' => $ticket->department_id,
                'pic_ticket' => auth()->user()->username,
                'response_date' => now(),
                'response_summary' => 'Ticket Cancelled',
                'response_descr' => 'Ticket cancelled by requester.',
                'status_pekerjaan' => 'CANCEL',
                'status' => 'A',
                'created_by' => auth()->user()->username,
            ]);


            $ticket->refresh();

            DB::connection('pgsql5')->commit();

            $this->notificationService
                ->ticketCancelled($ticket);



            return response()->json([
                'success' => true,
                'message' => 'Ticket cancelled successfully.',
            ]);
        } catch (\Throwable $th) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function detail($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);

        $ticket = TrTicket::with([
            'activities',
            'category',
            'subcategory',
            'priority',
            'location',
            'subLocation',
        ])->findOrFail($id);

        abort_unless(
            $this->canAccessTicket($ticket),
            403
        );

        /*
        |--------------------------------------------------------------------------
        | Attachments
        |--------------------------------------------------------------------------
        */

        $attachmentController =
            app(TrAttachmentController::class);

        $attachmentResponse =
            $attachmentController->listAttachments(
                request(),
                'TIC',
                $ticket->ticketid
            );

        $attachmentData =
            $attachmentResponse->getData(true);

        $attachments =
            $attachmentData['attachments'] ?? [];

        /*
        |--------------------------------------------------------------------------
        | Comments
        |--------------------------------------------------------------------------
        */

        $comments = TrMessage::query()
            ->where('refnbr', $ticket->ticketid)
            ->where('doctype', 'TIC')
            ->orderBy('created_at')
            ->get()
            ->map(function ($comment) {

                return [

                    'id' => $comment->id,

                    'message' => $comment->message,

                    'created_by' => $comment->created_by,

                    'created_at' => optional(
                        $comment->created_at
                    )->format('Y-m-d H:i:s'),

                ];
            });

        /*
        |--------------------------------------------------------------------------
        | Tracking Timeline
        |--------------------------------------------------------------------------
        */

        $tracking = $this->buildTracking(

            TrTicketActivity::where(
                'ticketid',
                $ticket->ticketid
            )
                ->orderBy('id')
                ->get(),

            $comments
        );

        return response()->json([

            'success' => true,

            'data' => [

                'ticket' => [

                    'id' =>
                    $ticket->id,

                    'eid' =>
                    Hashids::encode($ticket->id),

                    'ticketid' =>
                    $ticket->ticketid,

                    'ticketdate' =>
                    optional(
                        $ticket->ticketdate
                    )->format('Y-m-d H:i:s'),

                    'cpny_id' =>
                    $ticket->cpny_id,

                    'department_id' =>
                    $ticket->department_id,

                    'ticket_type' =>
                    $ticket->ticket_type,

                    'ticket_categoryid' =>
                    $ticket->ticket_categoryid,

                    'ticket_category' =>
                    optional(
                        $ticket->category
                    )->ticket_category_name,

                    'ticket_category_name' =>
                    optional(
                        $ticket->category
                    )->ticket_category_name,

                    'ticket_subcategoryid' =>
                    $ticket->ticket_subcategoryid,

                    'ticket_subcategory' =>
                    optional(
                        $ticket->subcategory
                    )->ticket_subcategory_name,

                    'ticket_subcategory_name' =>
                    optional(
                        $ticket->subcategory
                    )->ticket_subcategory_name,

                    'location_id' =>
                    $ticket->location_id,

                    'location_name' =>
                    optional(
                        $ticket->location
                    )->location_name,

                    'sub_location_id' =>
                    $ticket->sub_location_id,

                    'sub_location_name' =>
                    optional(
                        $ticket->subLocation
                    )->sub_location_name,

                    'ticket_priority' =>
                    $ticket->ticket_priority,

                    'priority_name' =>
                    optional(
                        $ticket->priority
                    )->ticket_priority_name,

                    'ticket_priority_name' =>
                    optional(
                        $ticket->priority
                    )->ticket_priority_name,

                    'ticket_duedate' =>
                    optional(
                        $ticket->ticket_duedate
                    )->format('Y-m-d H:i:s'),

                    'issue_summary' =>
                    $ticket->issue_summary,

                    'issue_descr' =>
                    $ticket->issue_descr,

                    'solution_descr' =>
                    $ticket->solution_descr,

                    'status' =>
                    $ticket->status,

                    'working_start_date' =>
                    optional(
                        $ticket->working_start_date
                    )->format('Y-m-d H:i:s'),

                    'working_end_date' =>
                    optional(
                        $ticket->working_end_date
                    )->format('Y-m-d H:i:s'),

                    'status_pekerjaan' =>
                    $ticket->status_pekerjaan,

                    'created_by' =>
                    $ticket->created_by,

                    'user_peminta' =>
                    $ticket->user_peminta,

                    'pic_ticket' =>
                    $ticket->pic_ticket,

                    'completed_by' =>
                    $ticket->completed_by,

                    'completed_at' =>
                    optional(
                        $ticket->completed_at
                    )->format('Y-m-d H:i:s'),

                ],

                'attachments' =>
                $attachments,

                'comments' =>
                $comments,

                'tracking' =>
                $tracking,

                'actions' =>
                $this->buildActions($ticket),

            ],

        ]);
    }

    public function tracking($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);

        $ticket = TrTicket::findOrFail($id);

        abort_unless(
            $this->canAccessTicket($ticket),
            403
        );

        $activities = TrTicketActivity::where(
            'ticketid',
            $ticket->ticketid
        )
            ->whereNull('deleted_at')
            ->orderBy('response_date')
            ->get();

        $comments = TrMessage::query()
            ->where('refnbr', $ticket->ticketid)
            ->where('doctype', 'TIC')
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $this->buildTracking(
                $activities,
                $comments
            ),
        ]);
    }

    public function responseTicket(Request $request, $hash)
    {
        abort_unless(
            $this->isITRole(),
            403
        );

        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);

        $ticket = TrTicket::findOrFail($id);

        abort_if(
            !$this->canTransition(
                $ticket->status_pekerjaan,
                'response'
            ),
            403
        );

        $request->validate([

            'pic_ticket' => 'required',

            'ticket_priority' => 'required',

            'response_descr' => 'required',

            'working_start_date' => 'nullable|date',

            'working_end_date' => 'nullable|date|after_or_equal:working_start_date',

        ]);

        abort_if(
            !$this->validatePIC(
                $request->pic_ticket,
                $ticket->ticket_type,
                $ticket->ticket_categoryid
            ),
            422,
            'Selected PIC is invalid.'
        );

        DB::connection('pgsql5')->beginTransaction();

        try {

            $priority = MsTicketPriority::where(
                'ticket_priority',
                $request->ticket_priority
            )->first();

            $dueDate = now()->addDays(
                $priority?->ticket_sla_days ?? 1
            );

            $workingStart =
                $request->working_start_date
                ?? now();

            $workingEnd =
                $request->working_end_date
                ?? $dueDate;

            $ticket->update([

                'pic_ticket' =>
                $request->pic_ticket,

                'ticket_priority' =>
                $request->ticket_priority,

                'ticket_sla_days' =>
                $priority?->ticket_sla_days,

                'ticket_duedate' =>
                $dueDate,

                'working_start_date' =>
                $workingStart,

                'working_end_date' =>
                $workingEnd,

                'status' => 'P',

                'status_pekerjaan' =>
                'RESPONSE',

                'updated_by' =>
                auth()->user()->username,
            ]);

            $this->createActivity([

                'ticketid' =>
                $ticket->ticketid,

                'cpny_id' =>
                $ticket->cpny_id,

                'department_id' =>
                $ticket->department_id,

                'pic_ticket' =>
                $request->pic_ticket,

                'response_date' =>
                now(),

                'response_summary' =>
                'Ticket Response',

                'response_descr' =>
                $request->response_descr,

                'working_start_date' =>
                $workingStart,

                'working_end_date' =>
                $workingEnd,

                'status_pekerjaan' =>
                'RESPONSE',

                'status' => 'A',

                'created_by' =>
                auth()->user()->username,
            ]);

            $ticket->refresh();

            DB::connection('pgsql5')->commit();

            $this->notificationService
                ->ticketAssigned($ticket);

            return response()->json([

                'success' => true,

                'message' =>
                'Ticket responded successfully.',
            ]);
        } catch (\Throwable $th) {

            DB::connection('pgsql5')->rollBack();

            return response()->json([

                'success' => false,

                'message' =>
                $th->getMessage(),

            ], 500);
        }
    }

    public function processTicket(Request $request, $hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);

        $ticket = TrTicket::findOrFail($id);

        abort_if(
            $ticket->pic_ticket !== auth()->user()->username,
            403
        );

        abort_if(
            $ticket->status !== 'P',
            403
        );

        abort_if(
            !$this->canTransition(
                $ticket->status_pekerjaan,
                'process'
            ),
            403
        );
        $request->validate([

            'response_descr' => 'required',

            'working_start_date' =>
            'nullable|date',

            'working_end_date' =>
            'nullable|date|after_or_equal:working_start_date',

            'attachments.*' => [
                'nullable',
                'file',
                'max:5120',
                'mimes:jpg,jpeg,png,pdf,xlsx,xls,doc,docx'
            ],

        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {

            /*
        |--------------------------------------------------------------------------
        | Working Schedule
        |--------------------------------------------------------------------------
        */

            $workingStart =
                $request->working_start_date
                ?? $ticket->working_start_date
                ?? now();

            $workingEnd =
                $request->working_end_date
                ?? $ticket->working_end_date
                ?? $ticket->ticket_duedate
                ?? now()->addDay();

            /*
        |--------------------------------------------------------------------------
        | Update Ticket
        |--------------------------------------------------------------------------
        */

            $ticket->update([

                'working_start_date' =>
                $workingStart,

                'working_end_date' =>
                $workingEnd,

                'status' => 'P',

                'status_pekerjaan' =>
                'PROCESS',

                'updated_by' =>
                auth()->user()->username,
            ]);

            /*
        |--------------------------------------------------------------------------
        | Create Activity
        |--------------------------------------------------------------------------
        */

            $this->createActivity([

                'ticketid' =>
                $ticket->ticketid,

                'cpny_id' =>
                $ticket->cpny_id,

                'department_id' =>
                $ticket->department_id,

                'pic_ticket' =>
                auth()->user()->username,

                'response_date' =>
                now(),

                'response_summary' =>
                'Ticket Process',

                'response_descr' =>
                $request->response_descr,

                'working_start_date' =>
                $workingStart,

                'working_end_date' =>
                $workingEnd,

                'status_pekerjaan' =>
                'PROCESS',

                'status' => 'A',

                'created_by' =>
                auth()->user()->username,
            ]);

            $uploadResult = null;

            if ($request->hasFile('attachments')) {

                $meta = [

                    'refnbr' => $ticket->ticketid,

                    'doctype' => 'TIC',

                    'cpny_id' => $ticket->cpny_id,

                    'department_id' => $ticket->department_id,

                    'base_folder' => 'att-ticket/tic-workflow',

                    'created_by' => auth()->user()->username,
                ];

                $files = (array) $request->file('attachments');

                try {

                    $uploader = app(
                        TrAttachmentController::class
                    );

                    $uploadResult =
                        $uploader->uploadInternal(
                            $meta,
                            $files
                        );
                } catch (\Throwable $e) {

                    DB::connection('pgsql5')
                        ->rollBack();

                    return response()->json([

                        'success' => false,

                        'message' => 'Failed upload attachment',

                        'error' => config('app.debug')
                            ? $e->getMessage()
                            : null,

                    ], 500);
                }
            }

            DB::connection('pgsql5')->commit();

            return response()->json([

                'success' => true,

                'message' =>
                'Ticket processed successfully.',

            ]);
        } catch (\Throwable $th) {

            DB::connection('pgsql5')->rollBack();

            return response()->json([

                'success' => false,

                'message' =>
                $th->getMessage(),

            ], 500);
        }
    }
    public function pendingTicket(Request $request, $hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);

        $ticket = TrTicket::findOrFail($id);

        abort_if(
            $ticket->pic_ticket !== auth()->user()->username,
            403
        );

        abort_if(
            $ticket->status !== 'P',
            403
        );

        abort_if(
            !$this->canTransition(
                $ticket->status_pekerjaan,
                'pending'
            ),
            403
        );

        $request->validate([

            'response_descr' => 'required',

            'working_start_date' =>
            'nullable|date',

            'working_end_date' =>
            'nullable|date|after_or_equal:working_start_date',

            'attachments.*' => [
                'nullable',
                'file',
                'max:5120',
                'mimes:jpg,jpeg,png,pdf,xlsx,xls,doc,docx'
            ],

        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {

            /*
        |--------------------------------------------------------------------------
        | Working Schedule
        |--------------------------------------------------------------------------
        */

            $workingStart =
                $request->working_start_date
                ?? $ticket->working_start_date
                ?? now();

            $workingEnd =
                $request->working_end_date
                ?? $ticket->working_end_date
                ?? $ticket->ticket_duedate
                ?? now()->addDay();

            /*
        |--------------------------------------------------------------------------
        | Update Ticket
        |--------------------------------------------------------------------------
        */

            $ticket->update([

                'working_start_date' =>
                $workingStart,

                'working_end_date' =>
                $workingEnd,

                'status' => 'P',

                'status_pekerjaan' =>
                'PENDING',

                'updated_by' =>
                auth()->user()->username,
            ]);

            /*
        |--------------------------------------------------------------------------
        | Create Activity
        |--------------------------------------------------------------------------
        */

            $this->createActivity([

                'ticketid' =>
                $ticket->ticketid,

                'cpny_id' =>
                $ticket->cpny_id,

                'department_id' =>
                $ticket->department_id,

                'pic_ticket' =>
                auth()->user()->username,

                'response_date' =>
                now(),

                'response_summary' =>
                'Ticket Pending',

                'response_descr' =>
                $request->response_descr,

                'working_start_date' =>
                $workingStart,

                'working_end_date' =>
                $workingEnd,

                'status_pekerjaan' =>
                'PENDING',

                'status' => 'A',

                'created_by' =>
                auth()->user()->username,
            ]);

            $uploadResult = null;

            if ($request->hasFile('attachments')) {

                $meta = [

                    'refnbr' => $ticket->ticketid,

                    'doctype' => 'TIC',

                    'cpny_id' => $ticket->cpny_id,

                    'department_id' => $ticket->department_id,

                    'base_folder' => 'att-ticket/tic-workflow',

                    'created_by' => auth()->user()->username,
                ];

                $files = (array) $request->file('attachments');

                try {

                    $uploader = app(
                        TrAttachmentController::class
                    );

                    $uploadResult =
                        $uploader->uploadInternal(
                            $meta,
                            $files
                        );
                } catch (\Throwable $e) {

                    DB::connection('pgsql5')
                        ->rollBack();

                    return response()->json([

                        'success' => false,

                        'message' => 'Failed upload attachment',

                        'error' => config('app.debug')
                            ? $e->getMessage()
                            : null,

                    ], 500);
                }
            }

            DB::connection('pgsql5')->commit();

            return response()->json([

                'success' => true,

                'message' =>
                'Ticket pending updated successfully.',

            ]);
        } catch (\Throwable $th) {

            DB::connection('pgsql5')->rollBack();

            return response()->json([

                'success' => false,

                'message' =>
                $th->getMessage(),

            ], 500);
        }
    }

    public function envisionTicket(Request $request, $hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);

        $ticket = TrTicket::findOrFail($id);

        abort_if(
            $ticket->pic_ticket !== auth()->user()->username,
            403
        );

        abort_if(
            $ticket->status !== 'P',
            403
        );

        abort_if(
            !$this->canTransition(
                $ticket->status_pekerjaan,
                'envision'
            ),
            403
        );

        $request->validate([

            'response_descr' => 'required',

            'working_start_date' =>
            'required|date',

            'working_end_date' =>
            'required|date|after_or_equal:working_start_date',

            'attachments.*' => [
                'nullable',
                'file',
                'max:5120',
                'mimes:jpg,jpeg,png,pdf,xlsx,xls,doc,docx'
            ],

        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {

            /*
        |--------------------------------------------------------------------------
        | Working Schedule
        |--------------------------------------------------------------------------
        */

            $workingStart =
                $request->working_start_date;

            $workingEnd =
                $request->working_end_date;

            /*
        |--------------------------------------------------------------------------
        | Update Ticket
        |--------------------------------------------------------------------------
        */

            $ticket->update([

                'working_start_date' =>
                $workingStart,

                'working_end_date' =>
                $workingEnd,

                'status' => 'P',

                'status_pekerjaan' =>
                'ENVISION',

                'updated_by' =>
                auth()->user()->username,
            ]);

            /*
        |--------------------------------------------------------------------------
        | Create Activity
        |--------------------------------------------------------------------------
        */

            $this->createActivity([

                'ticketid' =>
                $ticket->ticketid,

                'cpny_id' =>
                $ticket->cpny_id,

                'department_id' =>
                $ticket->department_id,

                'pic_ticket' =>
                auth()->user()->username,

                'response_date' =>
                now(),

                'response_summary' =>
                'Ticket Envision',

                'response_descr' =>
                $request->response_descr,

                'working_start_date' =>
                $workingStart,

                'working_end_date' =>
                $workingEnd,

                'status_pekerjaan' =>
                'ENVISION',

                'status' => 'A',

                'created_by' =>
                auth()->user()->username,
            ]);

            $uploadResult = null;

            if ($request->hasFile('attachments')) {

                $meta = [

                    'refnbr' => $ticket->ticketid,

                    'doctype' => 'TIC',

                    'cpny_id' => $ticket->cpny_id,

                    'department_id' => $ticket->department_id,

                    'base_folder' => 'att-ticket/tic-workflow',

                    'created_by' => auth()->user()->username,
                ];

                $files = (array) $request->file('attachments');

                try {

                    $uploader = app(
                        TrAttachmentController::class
                    );

                    $uploadResult =
                        $uploader->uploadInternal(
                            $meta,
                            $files
                        );
                } catch (\Throwable $e) {

                    DB::connection('pgsql5')
                        ->rollBack();

                    return response()->json([

                        'success' => false,

                        'message' => 'Failed upload attachment',

                        'error' => config('app.debug')
                            ? $e->getMessage()
                            : null,

                    ], 500);
                }
            }

            DB::connection('pgsql5')->commit();

            return response()->json([

                'success' => true,

                'message' =>
                'Ticket envision updated successfully.',

            ]);
        } catch (\Throwable $th) {

            DB::connection('pgsql5')->rollBack();

            return response()->json([

                'success' => false,

                'message' =>
                $th->getMessage(),

            ], 500);
        }
    }
    public function transferTicket(Request $request, $hash)
    {

        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);

        $ticket = TrTicket::findOrFail($id);

        abort_if(
            !$this->isITRole()
                &&
                $ticket->pic_ticket !== auth()->user()->username,
            403
        );


        abort_if(
            !$this->canTransition(
                $ticket->status_pekerjaan,
                'transfer'
            ),
            403
        );

        $request->validate([

            'ticket_type' =>
            'required',

            'ticket_categoryid' =>
            'required',

            'ticket_subcategoryid' =>
            'required',

            'pic_ticket' =>
            'nullable',

        ]);

        if ($request->filled('pic_ticket')) {

            abort_if(
                !$this->validatePIC(
                    $request->pic_ticket,
                    $ticket->ticket_type,
                    $ticket->ticket_categoryid
                ),
                422,
                'Selected PIC is invalid.'
            );
        }
        DB::connection('pgsql5')->beginTransaction();

        try {

            /*
        |--------------------------------------------------------------------------
        | Transfer Note
        |--------------------------------------------------------------------------
        */

            $transferNote = null;

            if (
                $ticket->ticket_categoryid != $request->ticket_categoryid
                || $ticket->ticket_subcategoryid != $request->ticket_subcategoryid
            ) {

                $oldCategory = optional(
                    MsTicketCategory::where(
                        'ticket_categoryid',
                        $ticket->ticket_categoryid
                    )->first()
                )->ticket_category_name;

                $oldSubcategory = optional(
                    MsTicketSubcategory::where(
                        'ticket_subcategoryid',
                        $ticket->ticket_subcategoryid
                    )->first()
                )->ticket_subcategory_name;

                $newCategory = optional(
                    MsTicketCategory::where(
                        'ticket_categoryid',
                        $request->ticket_categoryid
                    )->first()
                )->ticket_category_name;

                $newSubcategory = optional(
                    MsTicketSubcategory::where(
                        'ticket_subcategoryid',
                        $request->ticket_subcategoryid
                    )->first()
                )->ticket_subcategory_name;

                $transferNote =
                    "Transfer category from {$oldCategory} / {$oldSubcategory} to {$newCategory} / {$newSubcategory}";
            }

            /*
        |--------------------------------------------------------------------------
        | Reset Workflow Schedule
        |--------------------------------------------------------------------------
        */

            $workingStart = null;

            $workingEnd = null;

            /*
        |--------------------------------------------------------------------------
        | Update Ticket
        |--------------------------------------------------------------------------
        */

            $ticket->update([

                'ticket_type' =>
                $request->ticket_type,

                'ticket_categoryid' =>
                $request->ticket_categoryid,

                'ticket_subcategoryid' =>
                $request->ticket_subcategoryid,

                'pic_ticket' =>
                $request->pic_ticket,

                /*
            |--------------------------------------------------------------------------
            | Reset SLA
            |--------------------------------------------------------------------------
            */

                'ticket_priority' =>
                null,

                'ticket_sla_days' =>
                null,

                'ticket_duedate' =>
                null,

                /*
            |--------------------------------------------------------------------------
            | Reset Working Schedule
            |--------------------------------------------------------------------------
            */

                'working_start_date' =>
                $workingStart,

                'working_end_date' =>
                $workingEnd,

                /*
            |--------------------------------------------------------------------------
            | Workflow
            |--------------------------------------------------------------------------
            */

                'status' => 'P',

                'status_pekerjaan' =>
                'TRANSFER',

                'updated_by' =>
                auth()->user()->username,
            ]);

            /*
        |--------------------------------------------------------------------------
        | Create Activity
        |--------------------------------------------------------------------------
        */

            $this->createActivity([

                'ticketid' =>
                $ticket->ticketid,

                'cpny_id' =>
                $ticket->cpny_id,

                'department_id' =>
                $ticket->department_id,

                'pic_ticket' =>
                $request->pic_ticket,

                'response_date' =>
                now(),

                'response_summary' =>
                'Ticket Transfer',

                'response_descr' =>
                $transferNote,

                'working_start_date' =>
                $workingStart,

                'working_end_date' =>
                $workingEnd,

                'status_pekerjaan' =>
                'TRANSFER',

                'status' => 'A',

                'created_by' =>
                auth()->user()->username,
            ]);

            /*
        |--------------------------------------------------------------------------
        | Notification
        |--------------------------------------------------------------------------
        */

            $ticket->refresh();



            DB::connection('pgsql5')->commit();

            $this->notificationService
                ->ticketTransferred($ticket);

            return response()->json([

                'success' => true,

                'message' =>
                'Ticket transferred successfully.',

            ]);
        } catch (\Throwable $th) {

            DB::connection('pgsql5')->rollBack();

            return response()->json([

                'success' => false,

                'message' =>
                $th->getMessage(),

            ], 500);
        }
    }

    public function completeTicket(Request $request, $hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);

        $ticket = TrTicket::findOrFail($id);

        abort_if(
            $ticket->pic_ticket !== auth()->user()->username,
            403
        );

        abort_if(
            !$this->canTransition(
                $ticket->status_pekerjaan,
                'complete'
            ),
            403
        );

        $request->validate([
            'solution_descr' => 'required',

            'attachments.*' => [
                'nullable',
                'file',
                'max:5120',
                'mimes:jpg,jpeg,png,pdf,xlsx,xls,doc,docx'
            ],
        ]);



        DB::connection('pgsql5')->beginTransaction();

        try {
            $ticket->update([
                'solution_descr' => $request->solution_descr,
                'pic_completed_ticket' => Carbon::now(),
                'completed_by' => auth()->user()->username,
                'completed_at' => now(),
                'status' => 'C',
                'status_pekerjaan' => 'COMPLETED',
                'updated_by' => auth()->user()->username,
            ]);

            $this->createActivity([
                'ticketid' => $ticket->ticketid,
                'cpny_id' => $ticket->cpny_id,
                'department_id' => $ticket->department_id,
                'pic_ticket' => auth()->user()->username,
                'response_date' => now(),
                'response_summary' => 'Ticket Completed',
                'response_descr' => $request->solution_descr,
                'status_pekerjaan' => 'COMPLETED',
                'status' => 'A',
                'created_by' => auth()->user()->username,
            ]);

            $uploadResult = null;

            if ($request->hasFile('attachments')) {

                $meta = [

                    'refnbr' => $ticket->ticketid,

                    'doctype' => 'TIC',

                    'cpny_id' => $ticket->cpny_id,

                    'department_id' => $ticket->department_id,

                    'base_folder' => 'att-ticket/tic-workflow',

                    'created_by' => auth()->user()->username,
                ];

                $files = (array) $request->file('attachments');

                try {

                    $uploader = app(
                        TrAttachmentController::class
                    );

                    $uploadResult =
                        $uploader->uploadInternal(
                            $meta,
                            $files
                        );
                } catch (\Throwable $e) {

                    DB::connection('pgsql5')
                        ->rollBack();

                    return response()->json([

                        'success' => false,

                        'message' => 'Failed upload attachment',

                        'error' => config('app.debug')
                            ? $e->getMessage()
                            : null,

                    ], 500);
                }
            }

            $ticket->refresh();

            DB::connection('pgsql5')->commit();

            $this->notificationService
                ->ticketCompleted($ticket);


            return response()->json([
                'success' => true,
                'message' => 'Ticket completed successfully.',
            ]);
        } catch (\Throwable $th) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function reopenTicket(Request $request, $hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);

        $ticket = TrTicket::findOrFail($id);

        abort_unless(
            $this->isITRole(),
            403
        );

        abort_if(
            $ticket->status !== 'C'
            || $ticket->status_pekerjaan !== 'COMPLETED',
            403
        );

        $request->validate([

            'response_descr' =>
            'required',

            'working_start_date' =>
            'nullable|date',

            'working_end_date' =>
            'nullable|date|after_or_equal:working_start_date',

        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {

            /*
        |--------------------------------------------------------------------------
        | Working Schedule
        |--------------------------------------------------------------------------
        */

            $workingStart =
                $request->working_start_date
                ?? now();

            $workingEnd =
                $request->working_end_date
                ?? $ticket->ticket_duedate
                ?? now()->addDay();

            /*
        |--------------------------------------------------------------------------
        | Update Ticket
        |--------------------------------------------------------------------------
        */

            $ticket->update([

                'reopen_ticket' => now(),

                'reopen_descr' =>

                $request->response_descr,

                /*
            |--------------------------------------------------------------------------
            | Working Schedule
            |--------------------------------------------------------------------------
            */

                'working_start_date' =>

                $workingStart,

                'working_end_date' =>

                $workingEnd,

                /*
            |--------------------------------------------------------------------------
            | Workflow
            |--------------------------------------------------------------------------
            */

                'status' => 'P',

                'status_pekerjaan' =>

                'REOPEN',

                'updated_by' =>

                auth()->user()->username,

            ]);

            /*
        |--------------------------------------------------------------------------
        | Create Activity
        |--------------------------------------------------------------------------
        */

            $this->createActivity([

                'ticketid' =>

                $ticket->ticketid,

                'cpny_id' =>

                $ticket->cpny_id,

                'department_id' =>

                $ticket->department_id,

                'pic_ticket' =>

                auth()->user()->username,

                'response_date' =>

                now(),

                'response_summary' =>

                'Ticket Reopen',

                'response_descr' =>

                $request->response_descr,

                'working_start_date' =>

                $workingStart,

                'working_end_date' =>

                $workingEnd,

                'status_pekerjaan' =>

                'REOPEN',

                'status' => 'A',

                'created_by' =>

                auth()->user()->username,

            ]);

            /*
        |--------------------------------------------------------------------------
        | Notification
        |--------------------------------------------------------------------------
        */

            $ticket->refresh();

            DB::connection('pgsql5')->commit();

            $this->notificationService
                ->ticketReopened($ticket);

            return response()->json([

                'success' => true,

                'message' =>

                'Ticket reopened successfully.',

            ]);
        } catch (\Throwable $th) {

            DB::connection('pgsql5')->rollBack();

            return response()->json([

                'success' => false,

                'message' =>

                $th->getMessage(),

            ], 500);
        }
    }

    public function comments($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);

        $ticket = TrTicket::findOrFail($id);

        abort_unless(
            $this->canAccessTicket($ticket),
            403
        );

        $comments = TrMessage::query()
            ->where('refnbr', $ticket->ticketid)
            ->where('doctype', 'TIC')
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $comments,
        ]);
    }

    public function comment(Request $request, $hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);

        $ticket = TrTicket::findOrFail($id);

        abort_unless(
            $this->canAccessTicket($ticket),
            403
        );

        $request->validate([

            'message' => 'required',

            'attachments.*' => [

                'nullable',
                'file',
                'max:5120',
                'mimes:jpg,jpeg,png,pdf,xlsx,xls,doc,docx'

            ],

        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {


            $this->createActivity([

                'ticketid' => $ticket->ticketid,

                'cpny_id' => $ticket->cpny_id,

                'department_id' => $ticket->department_id,

                'pic_ticket' => auth()->user()->username,

                'response_date' => now(),

                'response_summary' => 'Ticket Comment',

                'response_descr' => $request->message,

                'status_pekerjaan' => $ticket->status_pekerjaan,

                'status' => 'A',

                'created_by' => auth()->user()->username,

            ]);

            TrMessage::create([

                'refnbr' =>
                $ticket->ticketid,

                'doctype' =>
                'TIC',

                'message_date' =>
                now(),

                'cpny_id' =>
                $ticket->cpny_id,

                'department_id' =>
                $ticket->department_id,

                'username' =>
                auth()->user()->username,

                'name' =>
                auth()->user()->name
                    ?? auth()->user()->username,

                'message' =>
                $request->message,

                'status' =>
                'A',

                'created_by' =>
                auth()->user()->username,

            ]);

            $uploadResult = null;

            if ($request->hasFile('attachments')) {

                $meta = [

                    'refnbr' => $ticket->ticketid,

                    'doctype' => 'TIC',

                    'cpny_id' => $ticket->cpny_id,

                    'department_id' => $ticket->department_id,

                    'base_folder' => 'att-ticket/tic-comment',

                    'created_by' => auth()->user()->username,

                ];

                $files = (array) $request->file('attachments');

                try {

                    $uploader = app(
                        TrAttachmentController::class
                    );

                    $uploadResult =
                        $uploader->uploadInternal(
                            $meta,
                            $files
                        );
                } catch (\Throwable $e) {

                    DB::connection('pgsql5')
                        ->rollBack();

                    return response()->json([

                        'success' => false,

                        'message' => 'Failed upload attachment',

                        'error' => config('app.debug')
                            ? $e->getMessage()
                            : null,

                    ], 500);
                }
            }

            /*
        |--------------------------------------------------------------------------
        | Notification
        |--------------------------------------------------------------------------
        */

            try {

                $ticket->refresh();

                if (
                    method_exists(
                        $this->notificationService,
                        'ticketCommented'
                    )
                ) {

                    $this->notificationService
                        ->ticketCommented(
                            $ticket,
                            auth()->user()->username,
                            $request->message
                        );
                }
            } catch (\Throwable $e) {

                \Log::warning(
                    'Ticket comment notification failed',
                    [
                        'ticketid' => $ticket->ticketid,
                        'error' => $e->getMessage(),
                    ]
                );
            }

            DB::connection('pgsql5')->commit();

            return response()->json([

                'success' => true,

                'message' => 'Comment sent successfully.',

            ]);
        } catch (\Throwable $th) {

            DB::connection('pgsql5')->rollBack();

            return response()->json([

                'success' => false,

                'message' => $th->getMessage(),

            ], 500);
        }
    }
    public function createDropdown()
    {
        $user = auth()->user();

        $companies = collect(
            explode(',', $user->cpny_id)
        )
            ->filter()
            ->map(function ($item) {
                return [
                    'cpny_id' => trim($item),
                    'cpny_name' => trim($item),
                ];
            })
            ->values();

        $departments = collect(
            explode(',', $user->department_id)
        )
            ->filter()
            ->map(function ($item) {
                return [
                    'department_id' => trim($item),
                    'department_name' => trim($item),
                ];
            })
            ->values();

        $userCompanies = explode(
            ',',
            $user->cpny_id
        );

        $locations = MsLocation::query()
            ->where('status', 'A')
            ->whereIn('cpny_id', $userCompanies)
            ->orderBy('location_name')
            ->get([
                'location_id',
                'location_name',
                'cpny_id',
            ]);

        $types = MsTicketType::query()
            ->where('status', 'A')
            ->orderBy('ticket_type_name')
            ->get([
                'ticket_type',
                'ticket_type_name',
            ]);

        return response()->json([
            'success' => true,
            'companies' => $companies,
            'departments' => $departments,
            'locations' => $locations,
            'types' => $types,
        ]);
    }

    public function categorySearch(Request $request)
    {
        $query = MsTicketCategory::query()
            ->where('status', 'A');

        if ($request->filled('ticket_type')) {
            $query->where('ticket_type', $request->ticket_type);
        }

        return response()->json([
            'results' => $query
                ->get()
                ->map(function ($row) {
                    return [
                        'id' => $row->ticket_categoryid,
                        'text' => $row->ticket_category_name,
                    ];
                }),
        ]);
    }

    public function subcategorySearch(Request $request)
    {
        $query = MsTicketSubcategory::query()
            ->where('status', 'A');

        if ($request->filled('ticket_type')) {
            $query->where('ticket_type', $request->ticket_type);
        }

        if ($request->filled('ticket_categoryid')) {
            $query->where('ticket_categoryid', $request->ticket_categoryid);
        }

        return response()->json([
            'results' => $query
                ->get()
                ->map(function ($row) {
                    return [
                        'id' => $row->ticket_subcategoryid,
                        'text' => $row->ticket_subcategory_name,
                    ];
                }),
        ]);
    }

    public function prioritySearch(Request $request)
    {
        $query = MsTicketPriority::query()
            ->where('status', 'A');

        if ($request->filled('ticket_type')) {
            $query->where('ticket_type', $request->ticket_type);
        }

        if ($request->filled('ticket_categoryid')) {
            $query->where('ticket_categoryid', $request->ticket_categoryid);
        }

        return response()->json([
            'results' => $query
                ->get()
                ->map(function ($row) {
                    return [
                        'id' => $row->ticket_priority,
                        'text' => $row->ticket_priority_name,
                        'sla_days' => $row->ticket_sla_days,
                    ];
                }),
        ]);
    }

    public function locationSearch(Request $request)
    {

        $query = MsLocation::query()
            ->where('status', 'A');

        return response()->json([

            'results' => $query
                ->orderBy('location_name')
                ->get()
                ->map(function ($row) {

                    return [

                        'id' => $row->location_id,

                        'text' => $row->location_name,

                    ];
                }),

        ]);
    }

    public function subLocationSearch(Request $request)
    {

        $query = MsSubLocation::query()
            ->where('status', 'A');

        if ($request->filled('location_id')) {

            $query->where(
                'location_id',
                $request->location_id
            );
        }

        return response()->json([

            'results' => $query
                ->orderBy('sub_location_name')
                ->get()
                ->map(function ($row) {

                    return [

                        'id' => $row->sub_location_id,

                        'text' => $row->sub_location_name,

                    ];
                }),

        ]);
    }

    public function picSearch(Request $request)
    {
        $query = MsTicketCategoryDept::query()
            ->where('status', 'A');

        if ($request->filled('ticket_type')) {

            $query->where(
                'ticket_type',
                $request->ticket_type
            );
        }

        if ($request->filled('ticket_categoryid')) {

            $query->where(
                'ticket_categoryid',
                $request->ticket_categoryid
            );
        }

        if ($request->filled('department_id')) {

            $query->where(
                'department_id',
                $request->department_id
            );
        }

        return response()->json([

            'results' => $query
                ->distinct()
                ->orderBy('username')
                ->get()
                ->map(function ($row) {

                    return [

                        'id' => $row->username,

                        'text' => $row->username,

                    ];
                }),

        ]);
    }

    protected function createActivity(array $data)
    {
        return TrTicketActivity::create([
            'ticketid' => $data['ticketid'],
            'cpny_id' => $data['cpny_id'],
            'department_id' => $data['department_id'],
            'pic_ticket' => $data['pic_ticket'] ?? null,
            'response_date' => $data['response_date'] ?? now(),
            'response_summary' => $data['response_summary'] ?? null,
            'response_descr' => $data['response_descr'] ?? null,
            'working_start_date' => $data['working_start_date'] ?? null,
            'working_end_date' => $data['working_end_date'] ?? null,
            'status_pekerjaan' => $data['status_pekerjaan'] ?? null,
            'status' => $data['status'] ?? 'A',
            'created_by' => $data['created_by'] ?? auth()->user()->username,
        ]);
    }

    protected function buildTracking($activities, $comments = [])
    {
        $timeline = collect();

        /*
    |--------------------------------------------------------------------------
    | Activity Timeline
    |--------------------------------------------------------------------------
    */

        foreach ($activities as $activity) {

            $timeline->push([

                'type' => 'activity',

                'title' =>
                $activity->response_summary
                    ?: 'Ticket Activity',

                'description' =>
                $activity->response_descr
                    ?: '-',

                'status' =>
                $activity->status_pekerjaan
                    ?: '-',

                'pic' =>
                $activity->pic_ticket
                    ?: $activity->created_by
                    ?: 'System',

                'datetime' =>
                $activity->response_date
                    ? \Carbon\Carbon::parse(
                        $activity->response_date
                    )->format('Y-m-d H:i:s')
                    : null,

                'working_start_date' =>
                $activity->working_start_date
                    ? \Carbon\Carbon::parse(
                        $activity->working_start_date
                    )->format('Y-m-d H:i:s')
                    : null,

                'working_end_date' =>
                $activity->working_end_date
                    ? \Carbon\Carbon::parse(
                        $activity->working_end_date
                    )->format('Y-m-d H:i:s')
                    : null,
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | Comment Timeline
    |--------------------------------------------------------------------------
    */

        foreach ($comments as $comment) {

            $timeline->push([

                'type' => 'comment',

                'title' =>
                'Ticket Comment',

                'description' =>
                $comment['message'] ?? '-',

                'status' =>
                'COMMENT',

                'pic' =>
                $comment['created_by']
                    ?? 'User',

                'datetime' =>
                $comment['created_at']
                    ?? now()->format('Y-m-d H:i:s'),

            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | Remove Duplicate Comment Activity
    |--------------------------------------------------------------------------
    */

        $timeline = $timeline->reject(function ($item) {

            return
                $item['type'] === 'activity'
                &&
                $item['title'] === 'Ticket Comment';
        });

        return $timeline
            ->sortBy('datetime')
            ->values();
    }

    protected function buildActions($ticket)
    {
        $user = auth()->user();

        $isRequester =
            $ticket->created_by === $user->username;

        $isPIC =
            $ticket->pic_ticket === $user->username;

        $isIT = $this->isITRole();

        return [
            'can_edit' => $isRequester
                && $ticket->status === 'P'
                && $ticket->status_pekerjaan === 'CREATED',

           'can_cancel' => (

                (
                    $isRequester
                    && $ticket->status === 'P'
                    && $ticket->status_pekerjaan === 'CREATED'
                )

                ||

                (
                    $isIT
                    && $ticket->status_pekerjaan !== 'COMPLETED'
                )

            ),

            'can_response' => $isIT
                && $ticket->status === 'P'
                && in_array($ticket->status_pekerjaan, [
                    'CREATED',
                    'REOPEN',
                    'TRANSFER',
                ]),

            'can_process' => $isPIC
                && $ticket->status === 'P'
                && in_array($ticket->status_pekerjaan, [
                    'RESPONSE',
                    'PENDING',
                    'ENVISION',
                ]),

            'can_pending' => $isPIC
                && $ticket->status === 'P'
                && in_array($ticket->status_pekerjaan, [
                    'PROCESS',
                ]),

            'can_envision' => $isPIC
                && $ticket->status === 'P'
                && in_array($ticket->status_pekerjaan, [
                    'PENDING',
                ]),

            'can_transfer' => (
                $isIT
                || $isPIC
            )
                && $ticket->status === 'P'
                && in_array($ticket->status_pekerjaan, [
                    'CREATED',
                    'RESPONSE',
                    'TRANSFER',
                    'REOPEN',
                ]),

            'can_complete' => $isPIC
                && $ticket->status === 'P'
                && in_array($ticket->status_pekerjaan, [
                    'PROCESS',
                    'PENDING',
                    'ENVISION',
                ]),

           'can_reopen' => $isIT
                && $ticket->status === 'C'
                && $ticket->status_pekerjaan === 'COMPLETED',
        ];
    }

    protected function validatePIC(
        $username,
        $ticketType,
        $categoryId
    ) {
        return MsTicketCategoryDept::query()
            ->where('username', $username)
            ->where('ticket_type', $ticketType)
            ->where('ticket_categoryid', $categoryId)
            ->where('status', 'A')
            ->exists();
    }
}
