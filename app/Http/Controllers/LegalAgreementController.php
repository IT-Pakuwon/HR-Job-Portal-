<?php

namespace App\Http\Controllers;

use App\Exports\AgreementExport;
use App\Exports\JobsExport;
use App\Http\Controllers\Traits\HasAutonbr;
use App\Models\MsCompany;
use App\Models\StagingContractAgreement;
use App\Models\SysUserRole;
use App\Models\TrAgreement;
use App\Models\TrAgreementActivity;
use App\Models\TrAgreementAttachment;
use App\Models\TrMessage;
use App\Models\User;
use App\Services\LegalAgreementNotificationService;
use Carbon\Carbon;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;

class LegalAgreementController extends Controller
{
    use HasAutonbr;

    protected $notificationService;

    public function __construct(
        LegalAgreementNotificationService $notificationService
    ) {
        $this->notificationService =
            $notificationService;
    }

    protected array $workflowTransitions = [

        'hold' => [
            'ACTIVE',
            'ESCALATED',
        ],

        'activate' => [
            'HOLD',
            'ESCALATED',
        ],

        'escalate' => [
            'ACTIVE',
        ],

        'complete' => [
            'ACTIVE',
            'ESCALATED',
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

        $userCompanies = $companies->pluck('cpny_id')->toArray();

        $isManager = $this->isManagerRole();

        $baseCount = function () use ($isManager, $userCompanies, $user) {
            $q = TrAgreement::query();
            if (!$isManager && !$user->hasFullDataScope()) {
                $q->where(function ($q2) use ($userCompanies, $user) {
                    $q2->whereIn('cpny_id', $userCompanies)
                       ->orWhere('created_user', $user->username)
                       ->orWhere(function ($q3) use ($user) {
                           $q3->wherePicLegalOrLeasing($user->username);
                       });
                });
            }
            return $q;
        };

        $counts = [
            'all' => $baseCount()->count(),

            'active' => $baseCount()->where(
                'agreement_step_id',
                'ACTIVE'
            )->count(),

            'hold' => $baseCount()->where(
                'agreement_step_id',
                'HOLD'
            )->count(),

            'escalated' => $baseCount()->where(
                'agreement_step_id',
                'ESCALATED'
            )->count(),

            'completed' => $baseCount()->where(
                'agreement_step_id',
                'COMPLETED'
            )->count(),

            'my_agreement' => TrAgreement::query()
                ->wherePicLegalOrLeasing($user->username)
                ->count(),

            'jobs_pending' => $this->pendingJobsCount(),
        ];

        $allCompanies = MsCompany::query()
            ->where('status', 'A')
            ->where('group_cpny_id', 'JKT')
            ->orderBy('cpny_name')
            ->get(['cpny_id', 'cpny_name']);

        $propertyTypes = StagingContractAgreement::query()
            ->whereNull('deleted_at')
            ->whereNotNull('property_cd')
            ->select('property_cd')
            ->distinct()
            ->orderBy('property_cd')
            ->pluck('property_cd');

        return view('pages.legal-agreement.agreement', [
            'title' => 'Legal Agreement',
            'eid' => $eid,
            'companies' => $companies,
            'counts' => $counts,
            'allCompanies' => $allCompanies,
            'propertyTypes' => $propertyTypes,
        ]);
    }

    public function json(Request $request)
    {
        $user = auth()->user();

        $isManager = $this->isManagerRole();

        $query = TrAgreement::query()
            ->whereNull('deleted_at');

        if (!$isManager && !$user->hasFullDataScope()) {
            $query->where(function ($q) use ($user) {
                $q->where('created_user', $user->username)
                    ->orWhere(function ($q2) use ($user) {
                        $q2->wherePicLegalOrLeasing($user->username);
                    });
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'MY_AGREEMENT') {
                $query->wherePicLegalOrLeasing($user->username);
            } else {
                $query->where('agreement_step_id', $request->status);
            }
        }

        if ($request->filled('status_filter')) {
            $query->where(
                'status',
                $request->status_filter
            );
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('agreement_id',   'ilike', "%{$search}%")
                  ->orWhere('business_name', 'ilike', "%{$search}%")
                  ->orWhere('trade_name',    'ilike', "%{$search}%")
                  ->orWhere('tenant_no',     'ilike', "%{$search}%")
                  ->orWhere('pic_legal',     'ilike', "%{$search}%")
                  ->orWhere('pic_leasing',   'ilike', "%{$search}%")
                  ->orWhere('created_user',  'ilike', "%{$search}%")
                  ->orWhere('cpny_id',       'ilike', "%{$search}%")
                  ->orWhere('agreement_step_id', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('cpny_id')) {
            $query->where(
                'cpny_id',
                $request->cpny_id
            );
        }

        if ($request->filled('date_from')) {
            $query->whereDate(
                'agreement_date',
                '>=',
                $request->date_from
            );
        }

        if ($request->filled('date_to')) {
            $query->whereDate(
                'agreement_date',
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

            ->make(true);
    }

    protected function isManagerRole()
    {
        return SysUserRole::query()
            ->where('username', auth()->user()->username)
            ->whereIn('role_id', ['LEGAL', 'LEGALMANAGER'])
            ->where('status', 'A')
            ->exists();
    }

    protected function canAccessAgreement($agreement)
    {
        $user = auth()->user();

        return
            $agreement->created_user === $user->username
            || $agreement->hasPic($user->username)
            || $this->isManagerRole();
    }

    public function store(Request $request)
    {
        $doctype = 'AGR';

        $user = $request->user();

        $username = $user->username ?? 'system';

        $dt = Carbon::now();

        $year = (int) $dt->year;

        $month = str_pad($dt->month, 2, '0', STR_PAD_LEFT);

        $request->validate([
            'cpny_id' => 'required',
            'business_id' => 'required',
            'business_name' => 'required|max:255',
            'tenant_no' => 'nullable|max:100',
            'property_cd' => 'nullable|max:20',
            'trade_name' => 'nullable|max:255',
            'floor_id' => 'nullable|max:50',
            'unit_id' => 'nullable|max:50',
            'business_address' => 'nullable',

            'site_id' => 'nullable',

            'pic_penyewa' => 'required|max:150',
            'pic_phonenumber_penyewa' => 'required|max:50',
            'pic_email_penyewa' => 'required|email',

            'pic_legal' => 'required|array|min:1',
            'pic_legal.*' => 'string',
            'pic_leasing' => 'nullable|array',
            'pic_leasing.*' => 'string',

            'no_psm_or_addendum' => 'required|max:150',
            'psm_or_addendum_date' => 'required|date',
            'psm_or_addendum_delivery_date' => 'required|date',

            'bukti_pengiriman' => 'required|array|min:1',
            'bukti_pengiriman.*' => [
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
                'AGR'
            );

            $urutan = (int) $auto['next'];

            $tglbln = substr((string) $year, 2).$month;

            $agreementId = $doctype.$tglbln.sprintf('%04d', $urutan);

            $isMall = strtoupper((string) $request->property_cd) === 'MALL';

            $agreement = TrAgreement::create([
                'agreement_id' => $agreementId,
                'renewal_sequence' => 1,
                'agreement_date' => now(),

                'cpny_id' => $request->cpny_id,
                'site_id' => $request->site_id,

                'business_id' => $request->business_id,
                'business_name' => $request->business_name,
                'tenant_no' => $request->tenant_no,
                'property_cd' => $request->property_cd,
                'trade_name' => $isMall ? $request->trade_name : null,
                'floor_id' => $request->floor_id,
                'unit_id' => $request->unit_id,
                'business_address' => $request->business_address,

                'pic_penyewa' => $request->pic_penyewa,
                'pic_phonenumber_penyewa' => $request->pic_phonenumber_penyewa,
                'pic_email_penyewa' => $request->pic_email_penyewa,

                'pic_legal' => TrAgreement::joinPicList((array) $request->pic_legal),
                'pic_leasing' => TrAgreement::joinPicList((array) $request->pic_leasing),

                'no_psm_or_addendum' => $request->no_psm_or_addendum,
                'psm_or_addendum_date' => $request->psm_or_addendum_date,
                'psm_or_addendum_delivery_date' => $request->psm_or_addendum_delivery_date,

                'agreement_step_id' => 'ACTIVE',
                'agreement_step_order' => 1,
                'agreement_step_created_user' => $username,
                'agreement_step_created_at' => now(),

                'status' => 'P',

                'created_user' => $username,
            ]);

            $this->createActivity([
                'agreement_id' => $agreement->agreement_id,
                'cpny_id' => $agreement->cpny_id,

                'response_date' => now(),

                'response_summary' => 'Agreement Created',

                'response_descr' => $agreement->business_name,

                'agreement_step_id' => 'ACTIVE',
                'agreement_step_order' => 1,

                'status_pekerjaan' => 'ACTIVE',

                'status' => 'A',

                'created_by' => $username,
            ]);

            if ($request->hasFile('bukti_pengiriman')) {
                try {
                    foreach ($request->file('bukti_pengiriman') as $file) {
                        $this->uploadAgreementAttachment(
                            $agreement,
                            $file,
                            $username,
                            'Bukti Pengiriman PSM/Addendum'
                        );
                    }
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

            // If this agreement was created off the back of a Jobs list entry,
            // mark the matching staging contract(s) as Completed so it drops
            // off the pending list.
            StagingContractAgreement::query()
                ->where('cpny_id', $agreement->cpny_id)
                ->where('business_id', $agreement->business_id)
                ->where('status', 'A')
                ->update([
                    'status' => 'C',
                    'updated_by' => $username,
                    'updated_at' => now(),
                ]);

            DB::connection('pgsql5')->commit();

            $this->notificationService
                ->agreementCreated($agreement);

            return response()->json([
                'success' => true,
                'message' => 'Agreement created successfully.',
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

        $agreement = TrAgreement::findOrFail($id);

        abort_if(
            $agreement->created_user !== auth()->user()->username,
            403
        );

        abort_if(
            $agreement->status !== 'P'
                || $agreement->agreement_step_id !== 'ACTIVE',
            403
        );

        $request->validate([
            'business_id' => 'required',
            'business_name' => 'required|max:255',
            'tenant_no' => 'nullable|max:100',
            'property_cd' => 'nullable|max:20',
            'trade_name' => 'nullable|max:255',
            'floor_id' => 'nullable|max:50',
            'unit_id' => 'nullable|max:50',
            'business_address' => 'nullable',

            'pic_penyewa' => 'required|max:150',
            'pic_phonenumber_penyewa' => 'required|max:50',
            'pic_email_penyewa' => 'nullable|email',

            'pic_legal' => 'nullable|array',
            'pic_legal.*' => 'string',
            'pic_leasing' => 'nullable|array',
            'pic_leasing.*' => 'string',

            'no_psm_or_addendum' => 'nullable|max:150',
            'psm_or_addendum_date' => 'nullable|date',
            'psm_or_addendum_delivery_date' => 'nullable|date',
        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {
            $isMall = strtoupper((string) $request->property_cd) === 'MALL';

            $agreement->update([
                'business_id' => $request->business_id,
                'business_name' => $request->business_name,
                'tenant_no' => $request->tenant_no,
                'property_cd' => $request->property_cd,
                'trade_name' => $isMall ? $request->trade_name : null,
                'floor_id' => $request->floor_id,
                'unit_id' => $request->unit_id,
                'business_address' => $request->business_address,

                'pic_penyewa' => $request->pic_penyewa,
                'pic_phonenumber_penyewa' => $request->pic_phonenumber_penyewa,
                'pic_email_penyewa' => $request->pic_email_penyewa,

                'pic_legal' => TrAgreement::joinPicList((array) $request->pic_legal),
                'pic_leasing' => TrAgreement::joinPicList((array) $request->pic_leasing),

                'no_psm_or_addendum' => $request->no_psm_or_addendum,
                'psm_or_addendum_date' => $request->psm_or_addendum_date,
                'psm_or_addendum_delivery_date' => $request->psm_or_addendum_delivery_date,

                'updated_user' => auth()->user()->username,
            ]);

            $this->createActivity([
                'agreement_id' => $agreement->agreement_id,
                'cpny_id' => $agreement->cpny_id,
                'response_date' => now(),
                'response_summary' => 'Agreement Updated',
                'response_descr' => $agreement->business_name,
                'agreement_step_id' => 'ACTIVE',
                'agreement_step_order' => $agreement->agreement_step_order,
                'status_pekerjaan' => 'ACTIVE',
                'status' => 'A',
                'created_by' => auth()->user()->username,
            ]);

            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Agreement updated successfully.',
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

        $agreement = TrAgreement::findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | Attachments
        |--------------------------------------------------------------------------
        */

        $attachments = $this->agreementAttachments($agreement);

        /*
        |--------------------------------------------------------------------------
        | Comments
        |--------------------------------------------------------------------------
        */

        $comments = TrMessage::query()
            ->where('refnbr', $agreement->agreement_id)
            ->where('doctype', 'AGR')
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
            TrAgreementActivity::where(
                'agreement_id',
                $agreement->agreement_id
            )
                ->orderBy('id')
                ->get(),

            $comments
        );

        return response()->json([
            'success' => true,

            'data' => [
                'agreement' => [
                    'id' => $agreement->id,

                    'eid' => Hashids::encode($agreement->id),

                    'agreement_id' => $agreement->agreement_id,

                    'renewal_sequence' => $agreement->renewal_sequence,

                    'agreement_date' => optional(
                        $agreement->agreement_date
                    )->format('Y-m-d H:i:s'),

                    'cpny_id' => $agreement->cpny_id,

                    'site_id' => $agreement->site_id,

                    'business_id' => $agreement->business_id,

                    'business_name' => $agreement->business_name,

                    'tenant_no' => $agreement->tenant_no,

                    'property_cd' => $agreement->property_cd,

                    'trade_name' => $agreement->trade_name,

                    'floor_id' => $agreement->floor_id,

                    'unit_id' => $agreement->unit_id,

                    'business_address' => $agreement->business_address,

                    'pic_penyewa' => $agreement->pic_penyewa,

                    'pic_phonenumber_penyewa' => $agreement->pic_phonenumber_penyewa,

                    'pic_email_penyewa' => $agreement->pic_email_penyewa,

                    'pic_legal' => $agreement->pic_legal,

                    'pic_legal_list' => $agreement->picLegalList(),

                    'pic_leasing' => $agreement->pic_leasing,

                    'pic_leasing_list' => $agreement->picLeasingList(),

                    'no_psm_or_addendum' => $agreement->no_psm_or_addendum,

                    'psm_or_addendum_date' => optional(
                        $agreement->psm_or_addendum_date
                    )->format('Y-m-d'),

                    'psm_or_addendum_delivery_date' => optional(
                        $agreement->psm_or_addendum_delivery_date
                    )->format('Y-m-d'),

                    'agreement_step_id' => $agreement->agreement_step_id,

                    'agreement_step_order' => $agreement->agreement_step_order,

                    'status' => $agreement->status,

                    'created_user' => $agreement->created_user,

                    'created_at' => optional(
                        $agreement->created_at
                    )->format('Y-m-d H:i:s'),
                ],

                'attachments' => $attachments,

                'comments' => $comments,

                'tracking' => $tracking,

                'actions' => $this->buildActions($agreement, $this->isManagerRole()),
            ],
        ]);
    }

    public function tracking($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);

        $agreement = TrAgreement::findOrFail($id);

        $activities = TrAgreementActivity::where(
            'agreement_id',
            $agreement->agreement_id
        )
            ->whereNull('deleted_at')
            ->orderBy('response_date')
            ->get();

        $comments = TrMessage::query()
            ->where('refnbr', $agreement->agreement_id)
            ->where('doctype', 'AGR')
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

    protected function transitionStep(
        TrAgreement $agreement,
        string $toStep,
        string $summary,
        ?string $descr,
        string $newStatus = 'P'
    ) {
        $agreement->update([
            'agreement_step_id' => $toStep,
            'agreement_step_order' => $agreement->agreement_step_order + 1,
            'agreement_step_created_user' => auth()->user()->username,
            'agreement_step_created_at' => now(),

            'status' => $newStatus,

            'updated_user' => auth()->user()->username,
        ]);

        $this->createActivity([
            'agreement_id' => $agreement->agreement_id,

            'cpny_id' => $agreement->cpny_id,

            'response_date' => now(),

            'response_summary' => $summary,

            'response_descr' => $descr,

            'agreement_step_id' => $toStep,
            'agreement_step_order' => $agreement->agreement_step_order,

            'status_pekerjaan' => $toStep,

            'status' => 'A',

            'created_by' => auth()->user()->username,
        ]);
    }

    public function holdAgreement(Request $request, $hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);

        $agreement = TrAgreement::findOrFail($id);

        abort_unless(
            $this->isManagerRole()
                || $agreement->hasPic(auth()->user()->username),
            403
        );

        abort_if(
            !$this->canTransition(
                $agreement->agreement_step_id,
                'hold'
            ),
            403
        );

        $request->validate([
            'response_descr' => 'required',
        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {
            $this->transitionStep(
                $agreement,
                'HOLD',
                'Agreement On Hold',
                $request->response_descr
            );

            $agreement->refresh();

            DB::connection('pgsql5')->commit();

            $this->notificationService
                ->agreementHeld($agreement);

            return response()->json([
                'success' => true,
                'message' => 'Agreement put on hold successfully.',
            ]);
        } catch (\Throwable $th) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function activateAgreement(Request $request, $hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);

        $agreement = TrAgreement::findOrFail($id);

        abort_unless(
            $this->isManagerRole()
                || $agreement->hasPic(auth()->user()->username),
            403
        );

        abort_if(
            !$this->canTransition(
                $agreement->agreement_step_id,
                'activate'
            ),
            403
        );

        $request->validate([
            'response_descr' => 'nullable',

            'pic_legal' => 'nullable|array',
            'pic_legal.*' => 'string',

            'pic_leasing' => 'nullable|array',
            'pic_leasing.*' => 'string',
        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {
            if ($request->filled('pic_legal')) {
                $agreement->pic_legal = TrAgreement::joinPicList((array) $request->pic_legal);
            }

            if ($request->filled('pic_leasing')) {
                $agreement->pic_leasing = TrAgreement::joinPicList((array) $request->pic_leasing);
            }

            $this->transitionStep(
                $agreement,
                'ACTIVE',
                'Agreement Activated',
                $request->response_descr
            );

            $agreement->refresh();

            DB::connection('pgsql5')->commit();

            $this->notificationService
                ->agreementActivated($agreement);

            return response()->json([
                'success' => true,
                'message' => 'Agreement activated successfully.',
            ]);
        } catch (\Throwable $th) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function escalateAgreement(Request $request, $hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);

        $agreement = TrAgreement::findOrFail($id);

        abort_unless(
            $this->isManagerRole()
                || $agreement->hasPic(auth()->user()->username),
            403
        );

        abort_if(
            !$this->canTransition(
                $agreement->agreement_step_id,
                'escalate'
            ),
            403
        );

        $request->validate([
            'response_descr' => 'required',
        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {
            $this->transitionStep(
                $agreement,
                'ESCALATED',
                'Agreement Escalated',
                $request->response_descr
            );

            $agreement->refresh();

            DB::connection('pgsql5')->commit();

            $this->notificationService
                ->agreementEscalated($agreement);

            return response()->json([
                'success' => true,
                'message' => 'Agreement escalated successfully.',
            ]);
        } catch (\Throwable $th) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function completeAgreement(Request $request, $hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);

        $agreement = TrAgreement::findOrFail($id);

        abort_unless(
            $this->isManagerRole()
                || $agreement->hasPic(auth()->user()->username),
            403
        );

        abort_if(
            !$this->canTransition(
                $agreement->agreement_step_id,
                'complete'
            ),
            403
        );

        $request->validate([
            'no_psm_or_addendum' => 'nullable|max:150',

            'psm_or_addendum_date' => 'nullable|date',

            'psm_or_addendum_delivery_date' => 'nullable|date',

            'response_descr' => 'required',

            'attachments.*' => [
                'nullable',
                'file',
                'max:5120',
                'mimes:jpg,jpeg,png,pdf,xlsx,xls,doc,docx',
            ],
        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {
            if ($request->filled('no_psm_or_addendum')) {
                $agreement->no_psm_or_addendum = $request->no_psm_or_addendum;
            }

            if ($request->filled('psm_or_addendum_date')) {
                $agreement->psm_or_addendum_date = $request->psm_or_addendum_date;
            }

            if ($request->filled('psm_or_addendum_delivery_date')) {
                $agreement->psm_or_addendum_delivery_date = $request->psm_or_addendum_delivery_date;
            }

            $this->transitionStep(
                $agreement,
                'COMPLETED',
                'Agreement Completed',
                $request->response_descr,
                'C'
            );

            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr' => $agreement->agreement_id,

                    'doctype' => 'AGR',

                    'cpny_id' => $agreement->cpny_id,

                    'base_folder' => 'att-legal-agreement/agr-workflow',

                    'created_by' => auth()->user()->username,
                ];

                $files = (array) $request->file('attachments');

                try {
                    $uploader = app(
                        TrAttachmentController::class
                    );

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

            $agreement->refresh();

            DB::connection('pgsql5')->commit();

            $this->notificationService
                ->agreementCompleted($agreement);

            return response()->json([
                'success' => true,
                'message' => 'Agreement completed successfully.',
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

        $agreement = TrAgreement::findOrFail($id);

        $comments = TrMessage::query()
            ->where('refnbr', $agreement->agreement_id)
            ->where('doctype', 'AGR')
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $comments,
        ]);
    }

    public function mentionableUsers($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);

        $agreement = TrAgreement::findOrFail($id);

        $usernames = collect([$agreement->created_user])
            ->merge($agreement->picLegalList())
            ->merge($agreement->picLeasingList())
            ->filter()
            ->map(fn ($u) => strtolower(trim($u)))
            ->unique()
            ->reject(fn ($u) => $u === strtolower(auth()->user()->username));

        $users = User::query()
            ->whereIn(DB::raw('lower(username)'), $usernames->all())
            ->get(['username', 'name'])
            ->values();

        return response()->json($users);
    }

    public function comment(Request $request, $hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);

        $agreement = TrAgreement::findOrFail($id);

        abort_unless(
            $this->canAccessAgreement($agreement),
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
                'agreement_id' => $agreement->agreement_id,

                'cpny_id' => $agreement->cpny_id,

                'response_date' => now(),

                'response_summary' => 'Agreement Comment',

                'response_descr' => $request->message,

                'agreement_step_id' => $agreement->agreement_step_id,
                'agreement_step_order' => $agreement->agreement_step_order,

                'status_pekerjaan' => $agreement->agreement_step_id,

                'status' => 'A',

                'created_by' => auth()->user()->username,
            ]);

            TrMessage::create([
                'refnbr' => $agreement->agreement_id,

                'doctype' => 'AGR',

                'message_date' => now(),

                'cpny_id' => $agreement->cpny_id,

                'username' => auth()->user()->username,

                'name' => auth()->user()->name
                    ?? auth()->user()->username,

                'message' => $request->message,

                'status' => 'A',

                'created_by' => auth()->user()->username,
            ]);

            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr' => $agreement->agreement_id,

                    'doctype' => 'AGR',

                    'cpny_id' => $agreement->cpny_id,

                    'base_folder' => 'att-legal-agreement/agr-comment',

                    'created_by' => auth()->user()->username,
                ];

                $files = (array) $request->file('attachments');

                try {
                    $uploader = app(
                        TrAttachmentController::class
                    );

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

            try {
                $agreement->refresh();

                if (
                    method_exists(
                        $this->notificationService,
                        'agreementCommented'
                    )
                ) {
                    $this->notificationService
                        ->agreementCommented(
                            $agreement,
                            auth()->user()->username,
                            $request->message
                        );
                }
            } catch (\Throwable $e) {
                \Log::warning(
                    'Agreement comment notification failed',
                    [
                        'agreement_id' => $agreement->agreement_id,
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
        $user      = auth()->user();
        $isManager = $this->isManagerRole();

        $userCompanies = collect(explode(',', $user->cpny_id))->filter()->map(fn($v) => trim($v))->toArray();

        $base = function () use ($isManager, $userCompanies, $user) {
            $q = TrAgreement::query();
            if (!$isManager && !$user->hasFullDataScope()) {
                $q->where(function ($q2) use ($userCompanies, $user) {
                    $q2->whereIn('cpny_id', $userCompanies)
                       ->orWhere('created_user', $user->username)
                       ->orWhere(function ($q3) use ($user) {
                           $q3->wherePicLegalOrLeasing($user->username);
                       });
                });
            }
            return $q;
        };

        $statuses = ['active', 'hold', 'escalated', 'completed'];

        $counts = ['all' => $base()->count()];

        foreach ($statuses as $s) {
            $counts[$s] = $base()->where('agreement_step_id', strtoupper($s))->count();
        }

        $counts['my_agreement'] = TrAgreement::query()
            ->wherePicLegalOrLeasing($user->username)
            ->count();

        $counts['jobs_pending'] = $this->pendingJobsCount();

        return response()->json($counts);
    }

    protected function pendingJobsCount(): int
    {
        // Row-level count (one row per contract/lot), matching how the Jobs
        // table itself counts — not deduped by business — so this badge
        // always agrees with what "Showing X of Y entries" shows there.
        return StagingContractAgreement::query()
            ->whereNull('deleted_at')
            ->where('status', 'A')
            ->count();
    }

    protected function applyJobsFilters($query, Request $request): void
    {
        if ($request->filled('cpny_id')) {
            $query->where('cpny_id', $request->cpny_id);
        }

        if ($request->filled('tenant_no')) {
            $query->where('tenant_no', 'ilike', "%{$request->tenant_no}%");
        }

        if ($request->filled('trade_name')) {
            $query->where('trade_name', 'ilike', "%{$request->trade_name}%");
        }

        if ($request->filled('property_cd')) {
            $query->where('property_cd', 'ilike', "%{$request->property_cd}%");
        }

        // Guarded to strings only: jobsJson() is hit by DataTables itself too,
        // which sends its own native search as a nested search[value] array —
        // that's handled automatically by DataTables::of() and must not be
        // re-applied here.
        if (is_string($request->search) && $request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('contract_no', 'ilike', "%{$search}%")
                    ->orWhere('tenant_no', 'ilike', "%{$search}%")
                    ->orWhere('trade_name', 'ilike', "%{$search}%")
                    ->orWhere('property_cd', 'ilike', "%{$search}%");
            });
        }
    }

    public function jobsJson(Request $request)
    {
        $query = StagingContractAgreement::query()
            ->whereNull('deleted_at')
            ->select([
                'id', 'cpny_id', 'business_id', 'contract_no',
                'tenant_no', 'trade_name', 'property_cd', 'status',
                'level_no', 'lot_no', 'mailing_addr', 'email_addr', 'email_addr2', 'name',
            ]);

        $this->applyJobsFilters($query, $request);

        return DataTables::of($query)->make(true);
    }

    public function jobsUpdateStatus(Request $request)
    {
        $request->validate([
            'cpny_id' => 'required',
            'business_id' => 'required',
            'status' => 'required|in:A,C,X',
        ]);

        // Applies to every lot row for this business, so the whole contract
        // moves together instead of showing a mixed status across its lots.
        StagingContractAgreement::query()
            ->where('cpny_id', $request->cpny_id)
            ->where('business_id', $request->business_id)
            ->update([
                'status' => $request->status,
                'updated_by' => auth()->user()->username,
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated.',
        ]);
    }

    public function jobsExport(Request $request)
    {
        return Excel::download(
            new JobsExport($request),
            'legal-agreement-jobs-export-'.now()->format('YmdHis').'.xlsx'
        );
    }

    public function createDropdown()
    {
        $companies = MsCompany::query()
            ->where('status', 'A')
            ->where('group_cpny_id', 'JKT')
            ->orderBy('cpny_name')
            ->get(['cpny_id', 'cpny_name']);

        return response()->json([
            'success' => true,
            'companies' => $companies,
        ]);
    }

    public function picSearch(Request $request)
    {
        $query = User::query()
            ->where('status', 'A');

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('username', 'ilike', "%{$search}%")
                  ->orWhere('name', 'ilike', "%{$search}%");
            });
        }

        // PIC Legal is restricted to users holding this role (e.g. LEGALACCESS);
        // when filtering by role we also switch the label to "Name - Email"
        // since that's what identifies a legal PIC, rather than their username.
        $roleId = $request->input('role_id');

        if ($roleId) {
            $query->whereIn('username', SysUserRole::where('role_id', $roleId)
                ->where('status', 'A')
                ->pluck('username'));
        }

        // PIC Leasing is further scoped to the agreement's company — ms_user.cpny_id
        // is a comma-separated list (a user can cover several companies), so this
        // matches any user whose list contains the given company, not just an
        // exact single-company match.
        if ($request->filled('cpny_id')) {
            $query->whereRaw("? = ANY(string_to_array(cpny_id, ','))", [$request->input('cpny_id')]);
        }

        return response()->json([
            'results' => $query
                ->orderBy('username')
                ->limit(20)
                ->get(['username', 'name', 'email'])
                ->map(function ($row) use ($roleId) {
                    if ($roleId) {
                        return [
                            'id' => $row->username,
                            'text' => $row->email
                                ? "{$row->name} - {$row->email}"
                                : $row->name,
                        ];
                    }

                    return [
                        'id' => $row->username,

                        'text' => $row->name
                            ? "{$row->username} - {$row->name}"
                            : $row->username,
                    ];
                }),
        ]);
    }

    public function companiesSearch(Request $request)
    {
        $companies = MsCompany::query()
            ->where('status', 'A')
            ->where('group_cpny_id', 'JKT')
            ->orderBy('cpny_name')
            ->get(['cpny_id', 'cpny_name']);

        return response()->json([
            'results' => $companies->map(fn($c) => [
                'id'   => $c->cpny_id,
                'text' => $c->cpny_name,
            ])->values(),
        ]);
    }

    protected function createActivity(array $data)
    {
        return TrAgreementActivity::create([
            'agreement_id' => $data['agreement_id'],
            'cpny_id' => $data['cpny_id'],
            'agreement_step_id' => $data['agreement_step_id'] ?? null,
            'agreement_step_order' => $data['agreement_step_order'] ?? null,
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

    /**
     * Uploads a file into the agreement's own GCS folder and records it in
     * tr_agreement_attachment (not the shared tr_attachment table other
     * modules use — this document type keeps its own attachment history).
     */
    protected function uploadAgreementAttachment(
        TrAgreement $agreement,
        UploadedFile $file,
        string $username,
        string $label
    ): TrAgreementAttachment {
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

        $yearFolder = 'att-agreement-legal/'.now()->year;

        $originalName = str_replace(['%', '\\', '/'], '', $file->getClientOriginalName());
        $randomPrefix = md5(random_int(1, 99999999));
        $ext = $file->getClientOriginalExtension();
        $filename = $randomPrefix.'.'.$ext;
        $gcsPath = "{$yearFolder}/{$filename}";

        $bucket->upload(
            fopen($file->getPathname(), 'r'),
            [
                'name' => $gcsPath,
                'predefinedAcl' => 'private',
                'metadata' => [
                    'contentType' => $file->getMimeType(),
                    'metadata' => ['original-name' => $originalName],
                ],
            ]
        );

        return TrAgreementAttachment::create([
            'agreement_id' => $agreement->agreement_id,
            'renewal_sequence' => $agreement->renewal_sequence,
            'attachment_date' => now(),
            'cpny_id' => $agreement->cpny_id,
            'attachment_name' => $label,
            'folder' => $yearFolder,
            'filename' => $filename,
            'filesize' => $file->getSize(),
            'extention' => $ext,
            'status' => 'A',
            'created_by' => $username,
        ]);
    }

    /**
     * Signed-URL listing for an agreement's own attachments (tr_agreement_attachment).
     */
    protected function agreementAttachments(TrAgreement $agreement): array
    {
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

        return TrAgreementAttachment::query()
            ->where('agreement_id', $agreement->agreement_id)
            ->where('status', 'A')
            ->orderBy('id')
            ->get()
            ->map(function ($row) use ($bucket) {
                $objectPath = rtrim($row->folder, '/').'/'.$row->filename;
                $signedUrl = null;

                try {
                    $signedUrl = $bucket->object($objectPath)->signedUrl(
                        new \DateTimeImmutable('+10 minutes'),
                        ['version' => 'v4']
                    );
                } catch (\Throwable $e) {
                    \Log::warning('Agreement attachment signed URL failed', [
                        'path' => $objectPath,
                        'error' => $e->getMessage(),
                    ]);
                }

                return [
                    'id' => $row->id,
                    'name' => $row->attachment_name,
                    'display_name' => $row->attachment_name,
                    'created_by' => $row->created_by,
                    'created_at' => optional($row->created_at)->toDateTimeString(),
                    'extention' => $row->extention,
                    'size' => $row->filesize,
                    'url' => $signedUrl,
                ];
            })
            ->all();
    }

    protected function buildTracking($activities, $comments = [])
    {
        $timeline = collect();

        foreach ($activities as $activity) {
            $timeline->push([
                'type' => 'activity',

                'title' => $activity->response_summary
                    ?: 'Agreement Activity',

                'description' => $activity->response_descr
                    ?: '-',

                'status' => $activity->status_pekerjaan
                    ?: '-',

                'submitted_by' => $activity->created_by
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

        foreach ($comments as $comment) {
            $timeline->push([
                'type' => 'comment',

                'title' => 'Agreement Comment',

                'description' => $comment['message'] ?? '-',

                'status' => 'COMMENT',

                'pic' => $comment['created_by']
                    ?? 'User',

                'datetime' => $comment['created_at']
                    ?? now()->format('Y-m-d H:i:s'),
            ]);
        }

        $timeline = $timeline->reject(function ($item) {
            return
                $item['type'] === 'activity'
                && $item['title'] === 'Agreement Comment';
        });

        return $timeline
            ->sortBy('datetime')
            ->values();
    }

    protected function buildActions($agreement, $isManager)
    {
        $user = auth()->user();

        $isRequester =
            $agreement->created_user === $user->username;

        $isPIC = $agreement->hasPic($user->username);

        $canAct = $isManager || $isPIC;

        return [
            'can_edit' => $isRequester
                && $agreement->status === 'P'
                && $agreement->agreement_step_id === 'ACTIVE',

            'can_hold' => $canAct
                && $this->canTransition($agreement->agreement_step_id, 'hold'),

            'can_activate' => $canAct
                && $this->canTransition($agreement->agreement_step_id, 'activate'),

            'can_escalate' => $canAct
                && $this->canTransition($agreement->agreement_step_id, 'escalate'),

            'can_complete' => $canAct
                && $this->canTransition($agreement->agreement_step_id, 'complete'),
        ];
    }

    public function printAgreement(string $hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);

        $agreement = TrAgreement::findOrFail($id);

        $attachments = $this->agreementAttachments($agreement);

        $pdf = \PDF::loadView('pages.legal-agreement.print', compact('agreement', 'attachments'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("AGREEMENT-{$agreement->agreement_id}.pdf");
    }

    public function export(Request $request)
    {
        abort_unless(
            $this->isManagerRole(),
            403
        );

        return Excel::download(
            new AgreementExport($request),
            'legal-agreement-export-'.now()->format('YmdHis').'.xlsx'
        );
    }
}
