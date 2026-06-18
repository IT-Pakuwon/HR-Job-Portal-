<?php

namespace App\Http\Controllers;

use App\Exports\TicketExport;
use App\Http\Controllers\Traits\HasAutonbr;
use App\Models\MsCompany;
use App\Models\MsLocation;
use App\Models\MsSubLocation;
use App\Models\MsTicketCategory;
use App\Models\MsTicketCategoryDept;
use App\Models\SysUserRole;
use App\Models\MsTicketPriority;
use App\Models\MsTicketSubcategory;
use App\Models\MsTicketType;
use App\Models\SysCalendar;
use App\Models\TrMessage;
use App\Models\TrServiceorderEnvision;
use App\Models\TrTicket;
use App\Models\TrTicketActivity;
use App\Services\TicketNotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;

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
        ],

        'process' => [
            'RESPONSE',
            'REOPEN',
            'PENDING',
            'ENVISION',
        ],

        'pending' => [
            'PROCESS',
        ],

        'envision' => [
            'PENDING',
            'PROCESS',
        ],

        'ENVISION CHECKED / SOLVED' => [
            'ENVISION',
        ],

        'complete' => [
            'PROCESS',
            'PENDING',
            'ENVISION CHECKED / SOLVED',
        ],

        'transfer' => [
            'CREATED',
            'TRANSFER',
            'REOPEN',
            'RESPONSE',
        ],

        'reopen' => [
            'COMPLETED',
            'CANCEL',
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

        $isIT = $this->isITRole();

        $userCompanies    = $companies->pluck('cpny_id')->toArray();
        $userDepartments  = $departments->pluck('department_id')->toArray();

        $baseCount = function () use ($isIT, $userCompanies, $userDepartments) {
            $q = TrTicket::query();
            if (!$isIT) {
                $q->whereIn('cpny_id', $userCompanies)
                  ->whereIn('department_id', $userDepartments);
            }
            return $q;
        };

        $counts = [
            'all' => $baseCount()->count(),

            'created' => $baseCount()->where(
                'status_pekerjaan',
                'CREATED'
            )->count(),

            'response' => $baseCount()->where(
                'status_pekerjaan',
                'RESPONSE'
            )->count(),

            'process' => $baseCount()->where(
                'status_pekerjaan',
                'PROCESS'
            )->count(),

            'pending' => $baseCount()->where(
                'status_pekerjaan',
                'PENDING'
            )->count(),

            'envision' => $baseCount()->where(
                'status_pekerjaan',
                'ENVISION'
            )->count(),

            'transfer' => $baseCount()->where(
                'status_pekerjaan',
                'TRANSFER'
            )->count(),

            'completed' => $baseCount()->where(
                'status_pekerjaan',
                'COMPLETED'
            )->count(),

            'reopen' => $baseCount()->where(
                'status_pekerjaan',
                'REOPEN'
            )->count(),

            'cancel' => $baseCount()->where(
                'status_pekerjaan',
                'CANCEL'
            )->count(),

            'ENVISION CHECKED / SOLVED' => $baseCount()->where(
                'status_pekerjaan',
                'ENVISION CHECKED / SOLVED'
            )->count(),

            'my_ticket' => TrTicket::query()
                ->where('pic_ticket', $user->username)
                ->count(),
        ];

        $categories = MsTicketCategory::query()
            ->where('status', 'A')
            ->orderBy('ticket_category_name')
            ->get(['ticket_categoryid', 'ticket_category_name']);

        $allCompanies = MsCompany::query()
            ->where('status', 'A')
            ->orderBy('cpny_name')
            ->get(['cpny_id', 'cpny_name']);

        return view('pages.ticket.ticket', [
            'title' => 'Ticket',
            'eid' => $eid,
            'companies' => $companies,
            'departments' => $departments,
            'counts' => $counts,
            'categories' => $categories,
            'allCompanies' => $allCompanies,
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
            'responseActivity',
        ])->whereNull('deleted_at');

        if (!$isIT) {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->username)
                    ->orWhere('pic_ticket', $user->username);
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'MY_TICKET') {
                $query->where('pic_ticket', $user->username);
            } else {
                $query->where('status_pekerjaan', $request->status);
            }
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
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('ticketid',        'ilike', "%{$search}%")
                  ->orWhere('issue_summary', 'ilike', "%{$search}%")
                  ->orWhere('pic_ticket',    'ilike', "%{$search}%")
                  ->orWhere('ticket_type',   'ilike', "%{$search}%")
                  ->orWhere('created_by',    'ilike', "%{$search}%")
                  ->orWhere('user_peminta',  'ilike', "%{$search}%")
                  ->orWhere('department_id', 'ilike', "%{$search}%")
                  ->orWhere('cpny_id',       'ilike', "%{$search}%")
                  ->orWhere('status_pekerjaan', 'ilike', "%{$search}%")
                  ->orWhereHas('category', fn ($c) => $c->where('ticket_categoryid', 'ilike', "%{$search}%"))
                  ->orWhereHas('subcategory', fn ($c) => $c->where('ticket_subcategoryid', 'ilike', "%{$search}%"));
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

        if ($request->filled('category_id')) {
            $query->where(
                'ticket_categoryid',
                $request->category_id
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

            ->addColumn('response_working_start', function ($row) {
                $ws = optional($row->responseActivity)->working_start_date;
                return $ws ? $ws->toISOString() : null;
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

    protected function isITByRole()
    {
        return SysUserRole::query()
            ->where('username', auth()->user()->username)
            ->whereIn('role_id', ['ITHARDWARE', 'ITSOFTWARE'])
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
                'mimes:jpg,jpeg,png,pdf,xlsx,xls,doc,docx',
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

            $tglbln = substr((string) $year, 2).$month;

            $ticketid = $doctype.$tglbln.sprintf('%04d', $urutan);

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

                'ticket_priority'  => 'Medium',
                'ticket_sla_days'  => $defaultPriority?->ticket_sla_days ?? 3,
                'ticket_duedate'   => $this->calculateDueDate($defaultPriority?->ticket_sla_days ?? 3),

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

            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
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

        $username    = auth()->user()->username;
        $isRequester = $ticket->created_by === $username;
        $isPIC       = $ticket->pic_ticket  === $username;
        $isITByRole  = $this->isITByRole();

        abort_if(
            !(
                ($isRequester && $ticket->status_pekerjaan === 'CREATED')
                || ($isPIC && $ticket->status_pekerjaan !== 'COMPLETED')
                || ($isITByRole && $ticket->status_pekerjaan !== 'COMPLETED')
            ),
            403
        );

        DB::connection('pgsql5')->beginTransaction();

        try {
            $ticket->update([
                'status' => 'X',
                'status_pekerjaan' => 'CANCEL',
                'updated_by' => $username,
            ]);

            $this->createActivity([
                'ticketid'         => $ticket->ticketid,
                'cpny_id'          => $ticket->cpny_id,
                'department_id'    => $ticket->department_id,
                'pic_ticket'       => $username,
                'response_date'    => now(),
                'response_summary' => 'Ticket Cancelled',
                'response_descr'   => 'Ticket cancelled.',
                'status_pekerjaan' => 'CANCEL',
                'status'           => 'A',
                'created_by'       => $username,
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

        $this->syncEnvisionSolved($ticket);

        $ticket->refresh();

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

        $serviceOrder = TrServiceorderEnvision::query()
            ->where(
                'ticketid',
                $ticket->ticketid
            )
            ->latest('serviceorderdate')
            ->first();

        return response()->json([
            'success' => true,

            'data' => [
                'ticket' => [
                    'id' => $ticket->id,

                    'eid' => Hashids::encode($ticket->id),

                    'ticketid' => $ticket->ticketid,

                    'ticketdate' => optional(
                        $ticket->ticketdate
                    )->format('Y-m-d H:i:s'),

                    'cpny_id' => $ticket->cpny_id,

                    'department_id' => $ticket->department_id,

                    'ticket_type' => $ticket->ticket_type,

                    'ticket_categoryid' => $ticket->ticket_categoryid,

                    'ticket_category' => optional(
                        $ticket->category
                    )->ticket_category_name,

                    'ticket_category_name' => optional(
                        $ticket->category
                    )->ticket_category_name,

                    'ticket_subcategoryid' => $ticket->ticket_subcategoryid,

                    'ticket_subcategory' => optional(
                        $ticket->subcategory
                    )->ticket_subcategory_name,

                    'ticket_subcategory_name' => optional(
                        $ticket->subcategory
                    )->ticket_subcategory_name,

                    'location_id' => $ticket->location_id,

                    'location_name' => optional(
                        $ticket->location
                    )->location_name,

                    'sub_location_id' => $ticket->sub_location_id,

                    'sub_location_name' => optional(
                        $ticket->subLocation
                    )->sub_location_name,

                    'ticket_priority' => $ticket->ticket_priority,

                    'priority_name' => optional(
                        $ticket->priority
                    )->ticket_priority_name,

                    'ticket_priority_name' => optional(
                        $ticket->priority
                    )->ticket_priority_name,

                    'ticket_duedate' => optional(
                        $ticket->ticket_duedate
                    )->format('Y-m-d H:i:s'),

                    'issue_summary' => $ticket->issue_summary,

                    'issue_descr' => $ticket->issue_descr,

                    'solution_descr' => $ticket->solution_descr,

                    'status' => $ticket->status,

                    'status_pekerjaan' => $ticket->status_pekerjaan,

                    'created_by' => $ticket->created_by,

                    'user_peminta' => $ticket->user_peminta,

                    'pic_ticket' => $ticket->pic_ticket,

                    'completed_by' => $ticket->completed_by,

                    'serviceorder_action' =>$serviceOrder?->serviceorder_action,

                    'completed_at' => optional(
                        $ticket->completed_at
                    )->format('Y-m-d H:i:s'),
                ],

                'attachments' => $attachments,

                'comments' => $comments,

                'tracking' => $tracking,

                'actions' => $this->buildActions($ticket),
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

            'response_descr' => 'nullable',

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

            $workingStart = $request->working_start_date
                ? Carbon::parse($request->working_start_date)
                : now();

            $workingEnd = $request->working_end_date ?? null;

            $ticket->update([
                'pic_ticket' => $request->pic_ticket,

                'ticket_priority' => $request->ticket_priority,

                'ticket_sla_days' => $priority?->ticket_sla_days,

                'status' => 'P',

                'status_pekerjaan' => 'RESPONSE',

                'updated_by' => auth()->user()->username,
            ]);

            $this->createActivity([
                'ticketid' => $ticket->ticketid,

                'cpny_id' => $ticket->cpny_id,

                'department_id' => $ticket->department_id,

                'pic_ticket' => $request->pic_ticket,

                'response_date' => now(),

                'response_summary' => 'Ticket Response',

                'response_descr' => $request->response_descr,

                'working_start_date' => $workingStart,

                'working_end_date' => $workingEnd,

                'status_pekerjaan' => 'RESPONSE',

                'status' => 'A',

                'created_by' => auth()->user()->username,
            ]);

            $ticket->refresh();

            DB::connection('pgsql5')->commit();

            $this->notificationService
                ->ticketAssigned($ticket);

            return response()->json([
                'success' => true,

                'message' => 'Ticket responded successfully.',
            ]);
        } catch (\Throwable $th) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,

                'message' => $th->getMessage(),
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
            'response_descr' => 'nullable',

            'cpny_id' => 'nullable|string',

            'working_start_date' => 'nullable|date',

            'working_end_date' => 'nullable|date|after_or_equal:working_start_date',

            'attachments.*' => [
                'nullable',
                'file',
                'max:5120',
                'mimes:jpg,jpeg,png,pdf,xlsx,xls,doc,docx',
            ],
        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {
            /*
        |--------------------------------------------------------------------------
        | Working Schedule
        |--------------------------------------------------------------------------
        */

            $workingStart = $request->working_start_date ?? now();

            $workingEnd = $request->working_end_date ?? $ticket->ticket_duedate ?? now()->addDay();

            /*
        |--------------------------------------------------------------------------
        | Update Ticket
        |--------------------------------------------------------------------------
        */

            $ticketUpdate = [
                'status' => 'P',

                'status_pekerjaan' => 'PROCESS',

                'updated_by' => auth()->user()->username,
            ];

            if ($request->filled('cpny_id')) {
                $ticketUpdate['cpny_id'] = $request->cpny_id;
            }

            $ticket->update($ticketUpdate);

            $ticket->refresh();

            /*
        |--------------------------------------------------------------------------
        | Create Activity
        |--------------------------------------------------------------------------
        */

            $this->createActivity([
                'ticketid' => $ticket->ticketid,

                'cpny_id' => $ticket->cpny_id,

                'department_id' => $ticket->department_id,

                'pic_ticket' => auth()->user()->username,

                'response_date' => now(),

                'response_summary' => 'Ticket Process',

                'response_descr' => $request->response_descr,

                'working_start_date' => $workingStart,

                'working_end_date' => $workingEnd,

                'status_pekerjaan' => 'PROCESS',

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

            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,

                'message' => 'Ticket processed successfully.',
            ]);
        } catch (\Throwable $th) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,

                'message' => $th->getMessage(),
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

            'working_start_date' => 'nullable|date',

            'working_end_date' => 'nullable|date|after_or_equal:working_start_date',

            'attachments.*' => [
                'nullable',
                'file',
                'max:5120',
                'mimes:jpg,jpeg,png,pdf,xlsx,xls,doc,docx',
            ],
        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {
            /*
        |--------------------------------------------------------------------------
        | Working Schedule
        |--------------------------------------------------------------------------
        */

            $workingStart = $request->working_start_date ?? now();

            $workingEnd = $request->working_end_date ?? $ticket->ticket_duedate ?? now()->addDay();

            /*
        |--------------------------------------------------------------------------
        | Update Ticket
        |--------------------------------------------------------------------------
        */

            $ticket->update([
                'status' => 'P',

                'status_pekerjaan' => 'PENDING',

                'updated_by' => auth()->user()->username,
            ]);

            /*
        |--------------------------------------------------------------------------
        | Create Activity
        |--------------------------------------------------------------------------
        */

            $this->createActivity([
                'ticketid' => $ticket->ticketid,

                'cpny_id' => $ticket->cpny_id,

                'department_id' => $ticket->department_id,

                'pic_ticket' => auth()->user()->username,

                'response_date' => now(),

                'response_summary' => 'Ticket Pending',

                'response_descr' => $request->response_descr,

                'working_start_date' => $workingStart,

                'working_end_date' => $workingEnd,

                'status_pekerjaan' => 'PENDING',

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

            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,

                'message' => 'Ticket pending updated successfully.',
            ]);
        } catch (\Throwable $th) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,

                'message' => $th->getMessage(),
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
            'response_summary' => 'required|string|max:255',
            'response_descr' => 'required',

            'working_start_date' => 'required|date',

            'working_end_date' => [
                'nullable',
                'date',
                'after_or_equal:working_start_date',
            ],

            'attachments.*' => [
                'nullable',
                'file',
                'max:5120',
                'mimes:jpg,jpeg,png,pdf,xlsx,xls,doc,docx',
            ],
        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {
            /*
        |--------------------------------------------------------------------------
        | Working Schedule
        |--------------------------------------------------------------------------
        */

            $workingStart = $request->working_start_date;

            $workingEnd = $request->working_end_date ?: null;

            /*
        |--------------------------------------------------------------------------
        | Update Ticket
        |--------------------------------------------------------------------------
        */

            $ticket->update([
                'status' => 'P',

                'status_pekerjaan' => 'ENVISION',

                'updated_by' => auth()->user()->username,
            ]);

            /*
        |--------------------------------------------------------------------------
        | Create Activity
        |--------------------------------------------------------------------------
        */

            $this->createActivity([
                'ticketid' => $ticket->ticketid,

                'cpny_id' => $ticket->cpny_id,

                'department_id' => $ticket->department_id,

                'pic_ticket' => auth()->user()->username,

                'response_date' => now(),

                'response_summary' => $request->response_summary,

                'response_descr' => $request->response_descr,

                'working_start_date' => $workingStart,

                'working_end_date' => $workingEnd,

                'status_pekerjaan' => 'ENVISION',

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

            DB::connection('pgsql5')->commit();

            $ticket->refresh();
        } catch (\Throwable $th) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,

                'message' => $th->getMessage(),
            ], 500);
        }

        try {
            $this->notificationService->ticketEnvision(
                $ticket,
                $request->response_summary,
                $request->response_descr
            );
        } catch (\Throwable $e) {
            \Log::error('ticketEnvision notification failed', [
                'ticketid' => $ticket->ticketid,
                'cpny_id'  => $ticket->cpny_id,
                'error'    => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Ticket envision updated successfully.',
        ]);
    }

    public function transferTicket(Request $request, $hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);

        $ticket = TrTicket::findOrFail($id);

        abort_if(
            !$this->isITRole()
                && $ticket->pic_ticket !== auth()->user()->username,
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
            'ticket_type' => 'required',

            'ticket_categoryid' => 'required',

            'ticket_subcategoryid' => 'required',

            'pic_ticket' => 'nullable',
        ]);

        if ($request->filled('pic_ticket')) {
            abort_if(
                !$this->validatePIC(
                    $request->pic_ticket,
                    $request->ticket_type,
                    $request->ticket_categoryid
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
                'ticket_type' => $request->ticket_type,

                'ticket_categoryid' => $request->ticket_categoryid,

                'ticket_subcategoryid' => $request->ticket_subcategoryid,

                'pic_ticket' => $request->pic_ticket,

                /*
            |--------------------------------------------------------------------------
            | Reset SLA
            |--------------------------------------------------------------------------
            */

                'ticket_sla_days' => null,

                'ticket_duedate' => null,

                /*
            |--------------------------------------------------------------------------
            | Reset Working Schedule
            |--------------------------------------------------------------------------
            */

                /*
            |--------------------------------------------------------------------------
            | Workflow
            |--------------------------------------------------------------------------
            */

                'status' => 'P',

                'status_pekerjaan' => 'TRANSFER',

                'updated_by' => auth()->user()->username,
            ]);

            /*
        |--------------------------------------------------------------------------
        | Create Activity
        |--------------------------------------------------------------------------
        */

            $this->createActivity([
                'ticketid' => $ticket->ticketid,

                'cpny_id' => $ticket->cpny_id,

                'department_id' => $ticket->department_id,

                'pic_ticket' => $request->pic_ticket,

                'response_date' => now(),

                'response_summary' => 'Ticket Transfer',

                'response_descr' => $transferNote,

                'working_start_date' => $workingStart,

                'working_end_date' => $workingEnd,

                'status_pekerjaan' => 'TRANSFER',

                'status' => 'A',

                'created_by' => auth()->user()->username,
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

                'message' => 'Ticket transferred successfully.',
            ]);
        } catch (\Throwable $th) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,

                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function completeTicket(Request $request, $hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);

        $ticket = TrTicket::findOrFail($id);

        $this->syncEnvisionSolved($ticket);

        $ticket->refresh();

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

            'working_start_date' => 'nullable|date',

            'working_end_date' => 'nullable|date|after_or_equal:working_start_date',

            'attachments.*' => [
                'nullable',
                'file',
                'max:5120',
                'mimes:jpg,jpeg,png,pdf,xlsx,xls,doc,docx',
            ],
        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {
            $ticket->update([
                'solution_descr' => $request->solution_descr,
                'pic_completed_ticket' => $request->working_end_date ?? now(),
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
                'working_start_date' => $request->working_start_date,
                'working_end_date' => $request->working_end_date,
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
            !(
                ($ticket->status === 'C' && $ticket->status_pekerjaan === 'COMPLETED')
                || ($ticket->status === 'X' && $ticket->status_pekerjaan === 'CANCEL')
            ),
            403
        );

        $request->validate([
            'response_descr' => 'required',

            'working_start_date' => 'nullable|date',

            'working_end_date' => 'nullable|date|after_or_equal:working_start_date',
        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {
            /*
        |--------------------------------------------------------------------------
        | Working Schedule
        |--------------------------------------------------------------------------
        */

            $workingStart = $request->working_start_date ?? now();

            $workingEnd = $request->working_end_date ?? $ticket->ticket_duedate ?? now()->addDay();

            /*
        |--------------------------------------------------------------------------
        | Update Ticket
        |--------------------------------------------------------------------------
        */

            $ticket->update([
                'reopen_ticket' => now(),

                'reopen_descr' => $request->response_descr,

                'status' => 'P',

                'status_pekerjaan' => 'REOPEN',

                'updated_by' => auth()->user()->username,
            ]);

            /*
        |--------------------------------------------------------------------------
        | Create Activity
        |--------------------------------------------------------------------------
        */

            $this->createActivity([
                'ticketid' => $ticket->ticketid,

                'cpny_id' => $ticket->cpny_id,

                'department_id' => $ticket->department_id,

                'pic_ticket' => auth()->user()->username,

                'response_date' => now(),

                'response_summary' => 'Ticket Reopen',

                'response_descr' => $request->response_descr,

                'working_start_date' => $workingStart,

                'working_end_date' => $workingEnd,

                'status_pekerjaan' => 'REOPEN',

                'status' => 'A',

                'created_by' => auth()->user()->username,
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

                'message' => 'Ticket reopened successfully.',
            ]);
        } catch (\Throwable $th) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,

                'message' => $th->getMessage(),
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
                'mimes:jpg,jpeg,png,pdf,xlsx,xls,doc,docx',
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
                'refnbr' => $ticket->ticketid,

                'doctype' => 'TIC',

                'message_date' => now(),

                'cpny_id' => $ticket->cpny_id,

                'department_id' => $ticket->department_id,

                'username' => auth()->user()->username,

                'name' => auth()->user()->name
                    ?? auth()->user()->username,

                'message' => $request->message,

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

            DB::connection('pgsql5')->commit();

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

    public function counts()
    {
        $user    = auth()->user();
        $isIT    = $this->isITRole();

        $userCompanies   = collect(explode(',', $user->cpny_id))->filter()->map(fn($v) => trim($v))->toArray();
        $userDepartments = collect(explode(',', $user->department_id))->filter()->map(fn($v) => trim($v))->toArray();

        $base = function () use ($isIT, $userCompanies, $userDepartments, $user) {
            $q = TrTicket::query();
            if (!$isIT) {
                $q->where(function ($q2) use ($user) {
                    $q2->where('created_by', $user->username)
                       ->orWhere('pic_ticket', $user->username);
                });
            }
            return $q;
        };

        $statuses = [
            'created', 'response', 'process', 'pending',
            'envision', 'transfer', 'completed', 'reopen', 'cancel',
        ];

        $counts = ['all' => $base()->count()];

        foreach ($statuses as $s) {
            $counts[$s] = $base()->where('status_pekerjaan', strtoupper($s))->count();
        }

        $counts['envision_solved'] = $base()
            ->where('status_pekerjaan', 'ENVISION CHECKED / SOLVED')
            ->count();

        $counts['my_ticket'] = TrTicket::query()
            ->where('pic_ticket', $user->username)
            ->count();

        return response()->json($counts);
    }

    public function createDropdown()
    {
        $user = auth()->user();

        $companies = MsCompany::query()
            ->where('status', 'A')
            ->orderBy('cpny_name')
            ->get(['cpny_id', 'cpny_name']);

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

        $locations = MsLocation::query()
            ->where('status', 'A')
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

        return response()->json([
            'results' => $query
                ->orderBy('ticket_priority_name')
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
        $user = auth()->user();

        $userCompanies = collect(
            explode(',', $user->cpny_id)
        )
            ->map(fn ($item) => trim($item))
            ->filter()
            ->values()
            ->toArray();

        $query = MsLocation::query()
            ->where('status', 'A')
            ->where(function ($q) use ($userCompanies) {
                $q->whereIn(
                    'cpny_id',
                    $userCompanies
                )
                ->orWhere(
                    'cpny_id',
                    'ALL'
                );
            });

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

    public function companiesSearch(Request $request)
    {
        $companies = MsCompany::query()
            ->where('status', 'A')
            ->orderBy('cpny_name')
            ->get(['cpny_id', 'cpny_name']);

        return response()->json([
            'results' => $companies->map(fn($c) => [
                'id'   => $c->cpny_id,
                'text' => $c->cpny_name,
            ])->values(),
        ]);
    }

    protected function calculateDueDate(int $slaDays, ?Carbon $from = null): Carbon
    {
        $holidays = SysCalendar::whereIn('date_calendar_type', ['LIBUR_NASIONAL', 'CUTI_BERSAMA'])
            ->pluck('date_calendar')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        $date = ($from ?? now())->copy()->startOfDay();
        $counted = 0;

        while ($counted < $slaDays) {
            $date->addDay();

            if ($date->isWeekend()) {
                continue;
            }

            if (!in_array($date->format('Y-m-d'), $holidays)) {
                $counted++;
            }
        }

        return $date->endOfDay();
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

                'title' => $activity->response_summary
                    ?: 'Ticket Activity',

                'description' => $activity->response_descr
                    ?: '-',

                'status' => $activity->status_pekerjaan
                    ?: '-',

                'pic' => $activity->pic_ticket
                    ?: $activity->created_by
                    ?: 'System',

                'datetime' => $activity->response_date
                    ? Carbon::parse(
                        $activity->response_date
                    )->format('Y-m-d H:i:s')
                    : null,

                'working_start_date' => $activity->working_start_date
                    ? Carbon::parse(
                        $activity->working_start_date
                    )->format('Y-m-d H:i:s')
                    : null,

                'working_end_date' => $activity->working_end_date
                    ? Carbon::parse(
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

                'title' => 'Ticket Comment',

                'description' => $comment['message'] ?? '-',

                'status' => 'COMMENT',

                'pic' => $comment['created_by']
                    ?? 'User',

                'datetime' => $comment['created_at']
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
                && $item['title'] === 'Ticket Comment';
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

        $isIT       = $this->isITRole();
        $isITByRole = $this->isITByRole();

        return [
            'can_edit' => $isRequester
                && $ticket->status === 'P'
                && $ticket->status_pekerjaan === 'CREATED',

            'can_cancel' => (
                ($isRequester && $ticket->status_pekerjaan === 'CREATED')
                || ($isPIC && $ticket->status_pekerjaan !== 'COMPLETED')
                || ($isITByRole && $ticket->status_pekerjaan !== 'COMPLETED')
            ),

            'can_response' => $isIT
                && $ticket->status === 'P'
                && in_array($ticket->status_pekerjaan, [
                    'CREATED',
                    'TRANSFER',
                ]),

            'can_process' => $isPIC
                && $ticket->status === 'P'
                && in_array($ticket->status_pekerjaan, [
                    'RESPONSE',
                    'PENDING',
                    'REOPEN',
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
                    'PROCESS',
                ]),

            'can_transfer' => (
                $isIT
                || $isPIC
            )
                && $ticket->status === 'P'
                && in_array($ticket->status_pekerjaan, [
                    'CREATED',
                    'TRANSFER',
                    'REOPEN',
                    'RESPONSE',
                ]),

            'can_complete' => $isPIC
                && $ticket->status === 'P'
                && in_array($ticket->status_pekerjaan, [
                    'PROCESS',
                    'PENDING',
                    'ENVISION CHECKED / SOLVED',
                ]),

            'can_reopen' => $isIT
                && (
                    ($ticket->status === 'C' && $ticket->status_pekerjaan === 'COMPLETED')
                    || ($ticket->status === 'X' && $ticket->status_pekerjaan === 'CANCEL')
                ),
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

    public function printTicket(string $hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);

        $ticket = TrTicket::with([
            'type',
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

        $attachmentController = app(TrAttachmentController::class);

        $attachmentResponse = $attachmentController->listAttachments(
            request(),
            'TIC',
            $ticket->ticketid
        );

        $attachments = $attachmentResponse->getData(true)['attachments'] ?? [];

        $imageExts = ['jpg', 'jpeg', 'png'];
        foreach ($attachments as &$att) {
            $ext = strtolower($att['extention'] ?? '');
            if (in_array($ext, $imageExts) && !empty($att['url'])) {
                try {
                    $bytes = file_get_contents($att['url']);
                    $mime  = $ext === 'png' ? 'image/png' : 'image/jpeg';
                    $att['base64'] = 'data:' . $mime . ';base64,' . base64_encode($bytes);
                } catch (\Throwable $e) {
                    $att['base64'] = null;
                }
            }
        }
        unset($att);

        // Convert any image URLs embedded in Quill HTML fields to base64 for DomPDF
        foreach (['issue_descr', 'solution_descr'] as $field) {
            if (!empty($ticket->$field)) {
                $ticket->$field = preg_replace_callback(
                    '/<img([^>]+)src=["\']([^"\']+)["\']([^>]*)>/i',
                    function ($m) {
                        $src = $m[2];
                        if (str_starts_with($src, 'data:')) return $m[0];
                        try {
                            $bytes = file_get_contents($src);
                            $ext   = strtolower(pathinfo(parse_url($src, PHP_URL_PATH), PATHINFO_EXTENSION));
                            $mime  = $ext === 'png' ? 'image/png' : ($ext === 'gif' ? 'image/gif' : 'image/jpeg');
                            $b64   = 'data:' . $mime . ';base64,' . base64_encode($bytes);
                            return '<img' . $m[1] . 'src="' . $b64 . '"' . $m[3] . '>';
                        } catch (\Throwable $e) {
                            return $m[0];
                        }
                    },
                    $ticket->$field
                );
            }
        }

        $responseActivity = TrTicketActivity::where('ticketid', $ticket->ticketid)
            ->where('status_pekerjaan', 'RESPONSE')
            ->orderBy('id')
            ->first();

        $respondedBy = $responseActivity?->created_by ?? $ticket->pic_ticket;

        $pdf = \PDF::loadView('pages.ticket.print', compact('ticket', 'attachments', 'respondedBy'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("TICKET-{$ticket->ticketid}.pdf");
    }

    public function export(Request $request)
    {
        abort_unless(
            $this->isITRole(),
            403
        );

        return Excel::download(
            new TicketExport($request),
            'ticket-export-'.now()->format('YmdHis').'.xlsx'
        );
    }

    protected function hasEnvisionSO(TrTicket $ticket): bool
    {
        return TrServiceorderEnvision::query()
            ->where('ticketid', $ticket->ticketid)
            ->exists();
    }

  protected function syncEnvisionSolved(
    TrTicket $ticket
): void {

    if (
        $ticket->status_pekerjaan !== 'ENVISION'
    ) {
        return;
    }

    if (
        !$this->hasEnvisionSO($ticket)
    ) {
        return;
    }

    $so = TrServiceorderEnvision::query()
        ->where(
            'ticketid',
            $ticket->ticketid
        )
        ->latest('serviceorderdate')
        ->first();

    $ticket->update([
        'status_pekerjaan' => 'ENVISION CHECKED / SOLVED',
        'updated_by'       => 'SYSTEM',
    ]);

    $existsActivity =
        TrTicketActivity::query()
            ->where(
                'ticketid',
                $ticket->ticketid
            )
            ->where(
                'status_pekerjaan',
                'ENVISION CHECKED / SOLVED'
            )
            ->exists();

    if (!$existsActivity) {

        $this->createActivity([
            'ticketid'          => $ticket->ticketid,
            'cpny_id'           => $ticket->cpny_id,
            'department_id'     => $ticket->department_id,
            'pic_ticket'        => $ticket->pic_ticket,
            'response_date'     => now(),

            'response_summary'  => 'Envision Solved',

            'response_descr'    =>
                'Note: ' .
                ($so->serviceorder_action ?? '-'),

            'status_pekerjaan'  =>
                'ENVISION CHECKED / SOLVED',

            'status'            => 'A',

            'created_by'        => 'SYSTEM',
        ]);
    }
}

    public function serviceOrderJson(Request $request)
    {
        abort_unless($this->isITRole(), 403);

        $query = TrServiceorderEnvision::query()
            ->whereNull('deleted_at');

        if ($request->filled('search_so')) {
            $s = $request->search_so;
            $query->where(function ($q) use ($s) {
                $q->where('serviceorderid', 'ilike', "%{$s}%")
                  ->orWhere('ticketid',       'ilike', "%{$s}%")
                  ->orWhere('user_pic',        'ilike', "%{$s}%")
                  ->orWhere('job_type',        'ilike', "%{$s}%");
            });
        }

        if ($request->filled('so_job_status')) {
            $query->where('job_status', $request->so_job_status);
        }

        if ($request->filled('so_date_from')) {
            $query->whereDate('serviceorderdate', '>=', $request->so_date_from);
        }

        if ($request->filled('so_date_to')) {
            $query->whereDate('serviceorderdate', '<=', $request->so_date_to);
        }

        return DataTables::of($query->orderByDesc('serviceorderdate'))
            ->addColumn('ticket_link', function ($row) {
                return $row->ticketid;
            })
            ->toJson();
    }

    public function serviceOrderNonAktif(int $id)
    {
        abort_unless($this->isITRole(), 403);

        $so = TrServiceorderEnvision::findOrFail($id);

        abort_if($so->status === 'X', 422, 'Service order is already non-active.');

        $user = auth()->user();

        $so->update([
            'status'     => 'X',
            'updated_by' => $user->username,
        ]);

        return response()->json(['message' => 'Service order set to non-active.']);
    }
}
