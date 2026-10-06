<?php

namespace App\Http\Controllers;

use App\Models\Autonbr;
use App\Models\MsAgreementDocument;
use App\Models\MsCompany;
use App\Models\StagingContractAgreement;
use App\Models\TrAgreement;
use App\Models\TrAgreementActivity;
use App\Models\TrAgreementDocument;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;

class LegalNewAgreementController extends Controller
{
    protected const DOCTYPE = 'NAG';

    // PSM / OLA agreements are typed by property: Mall = PSM, Office = OLA. 'PEMBUATAN'
    // is the older catch-all (rows made before the split, or a property that is neither).
    protected const TYPE_PEMBUATAN = 'PEMBUATAN';

    protected const PSM_OLA_TYPES = ['PSM', 'OLA', self::TYPE_PEMBUATAN];

    protected const ACTIVITY_CREATE_PEMBUATAN = 'CREATE_PEMBUATAN';

    protected const ACTIVITY_UPDATE_PEMBUATAN = 'UPDATE_PEMBUATAN';

    protected const ACTIVITY_ATTACHMENT = 'ATTACHMENT';

    protected const ACTIVITY_DOCUMENTS = 'DOCUMENTS_UPDATE';

    protected const ACTIVITY_PROCESS = 'PROCESS_UPDATE';

    // Process sheet steps in display order. Routing steps read Out then In;
    // has_in = false means out-only. Stored per the processesFor() note.
    protected const PROCESS_STEPS = [
        'CREATE' => ['descr' => 'Create', 'group' => 'MAIN', 'has_in' => true],
        'PRINT' => ['descr' => 'Cetak', 'group' => 'MAIN', 'has_in' => true],
        'ROUTE_MARKETING' => ['descr' => 'Marketing/Leasing', 'group' => 'ROUTING', 'has_in' => true],
        'ROUTE_IVY' => ['descr' => 'Ibu Ivy', 'group' => 'ROUTING', 'has_in' => true],
        'ROUTE_TENANT' => ['descr' => 'Tenant', 'group' => 'ROUTING', 'has_in' => true],
        'ROUTE_DIREKTUR_1' => ['descr' => 'Direktur 1', 'group' => 'ROUTING', 'has_in' => true],
        'ROUTE_DIREKTUR_2' => ['descr' => 'Direktur 2', 'group' => 'ROUTING', 'has_in' => true],
        'SEND_TENANT' => ['descr' => 'Kirim 1 set ke Penyewa', 'group' => 'ROUTING', 'has_in' => false],
    ];
    // Process-sheet rows in tr_agreement_activity: type = this prefix + process_id.
    protected const PROCESS_PREFIX = 'PROCESS:';

    protected const STEP_ACTIVE = 'ACTIVE';

    protected const STEP_COMPLETED = 'COMPLETED';

    // Roles whose holders can be picked as PIC Legal / PIC Leasing.
    protected const ROLE_PIC_LEGAL = 'LEGALACCESS';

    protected const ROLE_PIC_LEASING = 'LEASINGACCESS';

    protected const ACTIVITY_COMPLETE = 'COMPLETE_PEMBUATAN';

    protected const ACTIVITY_CANCEL = 'CANCEL_PEMBUATAN';

    protected const ACTIVITY_REOPEN = 'REOPEN_PEMBUATAN';

    protected const STEP_CANCELLED = 'CANCELLED';

    protected const PROPERTY_TYPES = ['OFF' => 'Office', 'MALL' => 'Mall', 'APT' => 'Apartment', 'HOTEL' => 'Hotel'];

    protected const TYPE_ADDENDUM = 'ADDENDUM';

    /**
     * PSM / OLA and Addendum run on the same code. Which one a request is for
     * comes from its URL (/legal-new-agreement/addendum/...). Addendum has no
     * document checklist, and can also be started from an existing PSM / OLA.
     * 'r' holds the route names the views need.
     */
    protected function kind(): array
    {
        if (request()->is('legal-new-agreement/addendum*')) {
            return [
                'key' => 'addendum',
                'type' => self::TYPE_ADDENDUM,
                'doctype' => 'NAD',
                'label' => 'Addendum',
                'docs' => false,
                'page' => 'legal-new-agreement.addendum',
                'r' => [
                    'jobs_json' => 'legal-new-agreement.addendum.jobs.json',
                    'jobs_export' => 'legal-new-agreement.addendum.jobs.export',
                    'active_json' => 'legal-new-agreement.addendum.active.json',
                    'completed_json' => 'legal-new-agreement.addendum.completed.json',
                    'cancelled_json' => 'legal-new-agreement.addendum.cancelled.json',
                    'cancelled_export' => 'legal-new-agreement.addendum.cancelled.export',
                    'active_export' => 'legal-new-agreement.addendum.active.export',
                    'completed_export' => 'legal-new-agreement.addendum.completed.export',
                    'source_json' => 'legal-new-agreement.addendum.psm-ola.json',
                    'view' => 'legal-new-agreement.addendum.view',
                    'edit' => 'legal-new-agreement.addendum.edit',
                    'update' => 'legal-new-agreement.addendum.update',
                    'create' => 'legal-new-agreement.addendum.create',
                    'store' => 'legal-new-agreement.addendum.store',
                    'show' => 'legal-new-agreement.addendum.show',
                    'attachment' => 'legal-new-agreement.addendum.attachment',
                    'process' => 'legal-new-agreement.addendum.process',
                    'complete' => 'legal-new-agreement.addendum.complete',
                    'cancel' => 'legal-new-agreement.addendum.cancel',
                    'reopen' => 'legal-new-agreement.addendum.reopen',
                    'attachment_delete' => 'legal-new-agreement.addendum.attachment.delete',
                    'documents' => null,
                    'pic_search' => 'legal-new-agreement.addendum.pic-search',
                ],
            ];
        }

        return [
            'key' => 'psm-ola',
            'type' => self::PSM_OLA_TYPES,
            'doctype' => self::DOCTYPE,
            'label' => 'PSM / OLA',
            'docs' => true,
            'page' => 'legal-new-agreement.psm-ola',
            'r' => [
                'jobs_json' => 'legal-new-agreement.jobs.json',
                'jobs_export' => 'legal-new-agreement.jobs.export',
                'active_json' => 'legal-new-agreement.active.json',
                'completed_json' => 'legal-new-agreement.completed.json',
                'cancelled_json' => 'legal-new-agreement.cancelled.json',
                'cancelled_export' => 'legal-new-agreement.cancelled.export',
                'active_export' => 'legal-new-agreement.active.export',
                'completed_export' => 'legal-new-agreement.completed.export',
                'source_json' => null,
                'view' => 'legal-new-agreement.psm-ola.view',
                'edit' => 'legal-new-agreement.psm-ola.edit',
                'update' => 'legal-new-agreement.psm-ola.update',
                'create' => 'legal-new-agreement.psm-ola.create',
                'store' => 'legal-new-agreement.psm-ola.store',
                'show' => 'legal-new-agreement.psm-ola.show',
                'attachment' => 'legal-new-agreement.psm-ola.attachment',
                'process' => 'legal-new-agreement.psm-ola.process',
                'complete' => 'legal-new-agreement.psm-ola.complete',
                'cancel' => 'legal-new-agreement.psm-ola.cancel',
                'reopen' => 'legal-new-agreement.psm-ola.reopen',
                'attachment_delete' => 'legal-new-agreement.psm-ola.attachment.delete',
                'documents' => 'legal-new-agreement.psm-ola.documents',
                'pic_search' => 'legal-new-agreement.psm-ola.pic-search',
            ],
        ];
    }

    // Mall = PSM, Office = OLA; anything else keeps the old catch-all type.
    public static function psmOlaTypeFor(?string $propertyCd): string
    {
        return match (strtoupper(trim((string) $propertyCd))) {
            'MALL' => 'PSM',
            'OFF' => 'OLA',
            default => self::TYPE_PEMBUATAN,
        };
    }

    // PSM / OLAs an addendum can still be started from: not cancelled, and no ACTIVE addendum made from them yet.
    protected function psmOlaSourceQuery()
    {
        return $this->activeQuery([self::STEP_ACTIVE, self::STEP_COMPLETED], self::PSM_OLA_TYPES)
            ->whereNotExists(function ($sub) {
                $sub->selectRaw('1')
                    ->from('tr_agreement as ad')
                    ->whereNull('ad.deleted_at')
                    ->where('ad.agreement_step_id', self::STEP_ACTIVE)
                    ->where('ad.agreement_type', self::TYPE_ADDENDUM)
                    ->whereColumn('ad.prev_agreement_id', 'tr_agreement.agreement_id');
            });
    }

    // Counts for the tab cards; Addendum's Jobs card adds its second source.
    protected function tabCounts(): array
    {
        $kind = $this->kind();
        $jobs = app(LegalAgreementController::class)->pendingJobsCount((array) $kind['type']);

        $counts = [
            'jobs_pending' => $jobs,
            'active' => $this->activeQuery()->count(),
            'completed' => $this->activeQuery(self::STEP_COMPLETED)->count(),
            'cancelled' => $this->activeQuery(self::STEP_CANCELLED)->count(),
        ];

        if ($kind['key'] === 'addendum') {
            $counts['ifca_jobs'] = $jobs;
            $counts['psm_ola'] = $this->psmOlaSourceQuery()->count();
            $counts['jobs_pending'] = $jobs + $counts['psm_ola'];
        }

        return $counts;
    }

    // $eid is only set by the /create-agreement/{eid} deep link, which renders
    // this same page with the create modal opened for that job.
    public function psmOla(?string $eid = null, ?string $viewEid = null, string $viewTab = 'active')
    {
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

        return view('pages.legal-new-agreement.psm-ola', [
            'kind' => $this->kind(),
            'title' => $this->kind()['label'],
            'openEid' => $eid,
            'openViewEid' => $viewEid,
            'openViewTab' => $viewTab,
            'allCompanies' => $allCompanies,
            'propertyTypes' => $propertyTypes,
            'counts' => $this->tabCounts(),
        ]);
    }

    // /legal-new-agreement/{eid}: the list page with the view modal opened.
    public function psmOlaView(string $eid)
    {
        $agreement = $this->viewableAgreementOrFail($eid);

        return $this->psmOla(null, $eid, ['COMPLETED' => 'completed', 'CANCELLED' => 'cancelled'][$agreement->agreement_step_id] ?? 'active');
    }

    /**
     * Agreements shown in the Active / Completed tabs: PSM/OLA Pembuatan at a
     * given step. Visibility follows Agreement FU: legal managers and
     * full-data-scope users see everything, everyone else sees what they
     * created or are PIC on.
     */
    protected function activeQuery(string|array $step = self::STEP_ACTIVE, string|array|null $type = null)
    {
        $user = auth()->user();

        $query = TrAgreement::query()
            ->whereNull('deleted_at')
            ->whereIn('agreement_type', (array) ($type ?? $this->kind()['type']))
            ->whereIn('agreement_step_id', (array) $step);

        if (! app(LegalAgreementController::class)->isManagerRole() && ! $user->hasFullDataScope()) {
            $query->where(function ($q) use ($user) {
                $q->where('created_user', $user->username)
                    ->orWhere(fn ($q2) => $q2->wherePicLegalOrLeasing($user->username));
            });
        }

        return $query;
    }

    public function activeJson(Request $request)
    {
        return $this->listJson($request, $this->activeQuery());
    }

    public function completedJson(Request $request)
    {
        return $this->listJson($request, $this->activeQuery(self::STEP_COMPLETED));
    }

    public function cancelledJson(Request $request)
    {
        return $this->listJson($request, $this->activeQuery(self::STEP_CANCELLED));
    }

    // Company / Type / search filters shared by the list and its Excel export.
    protected function applyListFilters(Request $request, $query): void
    {
        if ($request->filled('cpny_id')) {
            $query->where('cpny_id', $request->cpny_id);
        }

        if (is_string($request->property_cd) && in_array($request->property_cd, ['OFF', 'MALL'], true)) {
            $query->where('property_cd', $request->property_cd);
        }

        // Guarded to strings only — DataTables' own search[value] is an array
        // and is applied by DataTables::of() itself.
        if (is_string($request->search) && $request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('agreement_id', 'ilike', "%{$search}%")
                    ->orWhere('business_name', 'ilike', "%{$search}%")
                    ->orWhere('trade_name', 'ilike', "%{$search}%")
                    ->orWhere('tenant_no', 'ilike', "%{$search}%")
                    ->orWhere('created_user', 'ilike', "%{$search}%");
            });
        }
    }

    protected function listJson(Request $request, $query)
    {
        $this->applyListFilters($request, $query);

        $companyNames = MsCompany::query()->pluck('cpny_name', 'cpny_id');

        return DataTables::of($query)
            ->addColumn('eid', fn ($row) => Hashids::encode($row->id))
            ->addColumn('cpny_name', fn ($row) => $companyNames->get($row->cpny_id, $row->cpny_id))
            ->addColumn('can_edit', fn ($row) => $row->canBeUpdatedBy((string) auth()->user()->username))
            ->make(true);
    }

    public function activeExport(Request $request)
    {
        return $this->listExport($request, self::STEP_ACTIVE, 'active');
    }

    public function completedExport(Request $request)
    {
        return $this->listExport($request, self::STEP_COMPLETED, 'completed');
    }

    public function cancelledExport(Request $request)
    {
        return $this->listExport($request, self::STEP_CANCELLED, 'cancelled');
    }

    // Same visibility and filters as the list, downloaded as Excel.
    protected function listExport(Request $request, string $step, string $name)
    {
        $kind = $this->kind();
        $query = $this->activeQuery($step);
        $this->applyListFilters($request, $query);

        $companyNames = MsCompany::query()->pluck('cpny_name', 'cpny_id');
        $statusLabel = ['COMPLETED' => 'Completed', 'CANCELLED' => 'Cancelled'][$step] ?? 'Active';

        $rows = $query->orderBy('agreement_date', 'desc')->orderBy('id', 'desc')->get()->map(fn (TrAgreement $a) => [
            $a->agreement_id,
            $a->agreement_type,
            optional($a->agreement_date)->format('Y-m-d'),
            $companyNames->get($a->cpny_id, $a->cpny_id),
            $a->business_name,
            $a->tenant_no,
            $a->trade_name,
            self::PROPERTY_TYPES[strtoupper((string) $a->property_cd)] ?? $a->property_cd,
            $a->floor_id,
            $a->unit_id,
            $a->no_psm_or_addendum,
            implode(', ', $this->picLabels($a->picLegalList())),
            implode(', ', $this->picLabels($a->picLeasingList())),
            $a->created_user,
            $statusLabel,
            $a->prev_agreement_id,
        ]);

        return Excel::download(
            new \App\Exports\PsmOlaAgreementsExport($rows),
            str_replace(' / ', '-', strtolower($kind['label'])).'-'.$name.'-'.now()->format('YmdHis').'.xlsx'
        );
    }

    /**
     * Step 1 (tenant data, editable except company) + step 2 (document
     * checklist) page for turning a pending staging job into a PSM/OLA
     * "Pembuatan" agreement.
     */
    public function createPsmOla(Request $request, string $eid)
    {
        $kind = $this->kind();
        $source = $this->sourceFor($request, $eid);

        $company = MsCompany::query()->where('cpny_id', $source['cpny_id'])->first(['cpny_id', 'cpny_name']);

        return view('pages.legal-new-agreement.psm-ola-create-form', [
            'kind' => $kind,
            'isEdit' => false,
            'actionUrl' => route($kind['r']['store'], $eid, false).($source['agreement'] ? '?src=psm' : ''),
            'subtitle' => $source['subtitle'],
            'psmOlaNo' => $source['agreement']?->no_psm_or_addendum,
            'fromPsmOla' => (bool) $source['agreement'],
            'values' => $source['values'],
            'picLegalSelected' => $this->picLabels($source['pic_legal']),
            'picLeasingSelected' => $this->picLabels($source['pic_leasing']),
            'cpnyId' => $source['cpny_id'],
            'picSearchUrl' => route($kind['r']['pic_search'], [], false),
            'docState' => [],
            'companyName' => $company?->cpny_name ?? $source['cpny_id'],
            'documents' => $this->checklistDocuments(),
            'propertyTypes' => self::PROPERTY_TYPES,
        ]);
    }

    /**
     * Related documents: a PSM / OLA lists the addendums made from it, an addendum
     * lists the PSM / OLA it came from. Only ones the user may see (same rules as
     * the lists), each linking to its own page.
     */
    protected function relatedFor(TrAgreement $agreement): array
    {
        $steps = [self::STEP_ACTIVE, self::STEP_COMPLETED];

        if ($this->kind()['key'] === 'addendum') {
            if (! $agreement->prev_agreement_id) {
                return [];
            }

            $rows = $this->activeQuery($steps, self::PSM_OLA_TYPES)->where('agreement_id', $agreement->prev_agreement_id)->get();
            $type = 'PSM / OLA';
            $path = '/legal-new-agreement/';
        } else {
            $rows = $this->activeQuery($steps, self::TYPE_ADDENDUM)
                ->where(fn ($q) => $q->where('prev_agreement_id', $agreement->agreement_id)->orWhere('parent_agreement_id', $agreement->agreement_id))
                ->orderBy('agreement_date')->orderBy('id')
                ->get();
            $type = 'Addendum';
            $path = '/legal-new-agreement/addendum/';
        }

        return $rows->map(fn (TrAgreement $r) => [
            'type' => $type,
            'agreement_id' => $r->agreement_id,
            'number' => $r->no_psm_or_addendum,
            'date' => optional($r->agreement_date)->format('Y-m-d'),
            'created_by' => $r->created_user,
            'completed' => $r->agreement_step_id === self::STEP_COMPLETED,
            'url' => $path.Hashids::encode($r->id),
        ])->all();
    }

    // An addendum made from a PSM / OLA shows that PSM / OLA's number.
    protected function psmOlaNoFor(TrAgreement $agreement): ?string
    {
        if ($this->kind()['key'] !== 'addendum' || ! $agreement->prev_agreement_id) {
            return null;
        }

        return TrAgreement::query()
            ->where('agreement_id', $agreement->prev_agreement_id)
            ->whereIn('agreement_type', self::PSM_OLA_TYPES)
            ->value('no_psm_or_addendum');
    }

    // The master checklist; Addendum has none.
    protected function checklistDocuments()
    {
        if (! $this->kind()['docs']) {
            return collect();
        }

        return MsAgreementDocument::query()
            ->where('status', 'A')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get(['agreementdocument_id', 'agreementdocument_descr', 'agreementdocument_required']);
    }

    /**
     * What a new agreement starts from: an IFCA staging job, or (Addendum only,
     * ?src=psm) an existing PSM / OLA whose tenant data and PICs carry over.
     */
    protected function sourceFor(Request $request, string $eid): array
    {
        if ($this->kind()['key'] === 'addendum' && $request->query('src') === 'psm') {
            $id = Hashids::decode($eid)[0] ?? null;

            abort_if(! $id, 404);

            $a = $this->psmOlaSourceQuery()->findOrFail($id);

            return [
                'job' => null,
                'agreement' => $a,
                'cpny_id' => $a->cpny_id,
                'subtitle' => 'Created from PSM / OLA: '.$a->agreement_id.' ('.($a->trade_name ?: $a->business_name).')',
                'values' => $a->only([
                    'business_id', 'tenant_no', 'trade_name', 'business_name', 'property_cd', 'floor_id', 'unit_id',
                    'business_address', 'pic_penyewa', 'pic_phonenumber_penyewa', 'pic_email_penyewa',
                ]) + ['no_psm_or_addendum' => null],
                'pic_legal' => $a->picLegalList(),
                'pic_leasing' => $a->picLeasingList(),
            ];
        }

        $job = $this->pendingJobOrFail($eid);

        return [
            'job' => $job,
            'agreement' => null,
            'cpny_id' => $job->cpny_id,
            'subtitle' => 'Created from job: '.($job->trade_name ?: $job->name).' ('.($job->tenant_no ?: '-').')',
            'values' => [
                'business_id' => $job->business_id,
                'tenant_no' => $job->tenant_no,
                'trade_name' => $job->trade_name,
                'business_name' => $job->name,
                'property_cd' => $job->property_cd,
                'floor_id' => $job->level_no,
                'unit_id' => $job->lot_no,
                'business_address' => $job->mailing_addr,
                'pic_penyewa' => null,
                'pic_phonenumber_penyewa' => null,
                'pic_email_penyewa' => trim((string) $job->email_addr) ?: trim((string) $job->email_addr2),
                'no_psm_or_addendum' => null,
            ],
            'pic_legal' => [],
            'pic_leasing' => [],
        ];
    }

    /**
     * Edit reuses the create modal: same three steps, prefilled from the saved
     * agreement and its document checklist, posting to the update endpoint.
     */
    public function editPsmOla(string $eid)
    {
        $agreement = $this->updatableAgreementOrFail($eid);

        $company = MsCompany::query()->where('cpny_id', $agreement->cpny_id)->first(['cpny_id', 'cpny_name']);

        $saved = TrAgreementDocument::query()
            ->where('agreement_id', $agreement->agreement_id)
            ->whereNull('deleted_at')
            ->get()
            ->keyBy('agreementdocument_id');

        // Master list drives the rows (as on create); saved rows fill the state.
        $kind = $this->kind();
        $documents = $this->checklistDocuments();

        return view('pages.legal-new-agreement.psm-ola-create-form', [
            'kind' => $kind,
            'isEdit' => true,
            'actionUrl' => route($kind['r']['update'], $eid, false),
            'subtitle' => $agreement->agreement_id.' · '.($agreement->trade_name ?: $agreement->business_name),
            'psmOlaNo' => $this->psmOlaNoFor($agreement),
            'fromPsmOla' => (bool) $agreement->prev_agreement_id,
            'values' => $agreement->only([
                'business_id', 'tenant_no', 'trade_name', 'business_name', 'property_cd', 'floor_id', 'unit_id',
                'business_address', 'pic_penyewa', 'pic_phonenumber_penyewa', 'pic_email_penyewa', 'no_psm_or_addendum',
            ]),
            'picLegalSelected' => $this->picLabels($agreement->picLegalList()),
            'picLeasingSelected' => $this->picLabels($agreement->picLeasingList()),
            'cpnyId' => $agreement->cpny_id,
            'picSearchUrl' => route($kind['r']['pic_search'], [], false),
            'docState' => $saved->map(fn ($d) => [
                'received' => (bool) $d->agreementdocument_received,
                'note' => $d->agreementdocument_note,
            ])->all(),
            'companyName' => $company?->cpny_name ?? $agreement->cpny_id,
            'documents' => $documents,
            'propertyTypes' => self::PROPERTY_TYPES,
        ]);
    }

    public function storePsmOla(Request $request, string $eid)
    {
        $kind = $this->kind();
        $source = $this->sourceFor($request, $eid);
        $job = $source['job'];

        $validDocIds = $this->checklistDocuments()->pluck('agreementdocument_id')->all();

        $request->validate([
            'business_id' => 'required|integer',
            'business_name' => 'required|max:255',
            'tenant_no' => 'nullable|max:20',
            'trade_name' => 'nullable|max:255',
            'property_cd' => 'nullable|max:20',
            'floor_id' => 'nullable|max:20',
            'unit_id' => 'nullable|max:20',
            'business_address' => 'nullable|max:500',
            'pic_penyewa' => 'nullable|max:255',
            'pic_phonenumber_penyewa' => 'nullable|max:50',
            'pic_email_penyewa' => 'nullable|email|max:255',

            'no_psm_or_addendum' => 'required|max:150',
            'pic_legal' => 'required|array|min:1',
            'pic_legal.*' => ['string', Rule::exists('pgsql2.sys_user_role', 'username')->where('role_id', self::ROLE_PIC_LEGAL)->where('status', 'A')],
            'pic_leasing' => 'required|array|min:1',
            'pic_leasing.*' => ['string', Rule::exists('pgsql2.sys_user_role', 'username')->where('role_id', self::ROLE_PIC_LEASING)->where('status', 'A')],

            'documents' => 'nullable|array',
            'documents.*.received' => 'nullable|boolean',
            'documents.*.note' => 'nullable|string|max:2000',
        ]);

        $submittedDocs = $kind['docs'] ? (array) $request->input('documents', []) : [];

        if (array_diff(array_keys($submittedDocs), $validDocIds)) {
            return response()->json(['success' => false, 'message' => 'Unknown document in checklist.'], 422);
        }

        if ($message = $this->duplicateMessage($request, null, (string) $source['cpny_id'])) {
            return response()->json(['success' => false, 'message' => $message], 422);
        }

        $username = $request->user()->username ?? 'system';
        $dt = Carbon::now();
        $year = (int) $dt->year;
        $month = str_pad($dt->month, 2, '0', STR_PAD_LEFT);

        DB::connection('pgsql5')->beginTransaction();

        try {
            $next = $this->nextAutonbr($kind['doctype'], $year, $month, $username, 'New Agreement '.$kind['label']);

            // Same id shape as Item Request: DOCTYPE + yymm + 4-digit number.
            $agreementId = $kind['doctype'].substr((string) $year, 2).$month.sprintf('%04d', $next);

            $agreement = TrAgreement::create([
                'agreement_id' => $agreementId,
                'renewal_sequence' => 1,
                'agreement_date' => now(),
                'agreement_type' => $kind['key'] === 'addendum' ? self::TYPE_ADDENDUM : self::psmOlaTypeFor($request->property_cd),

                // An addendum started from a PSM / OLA points back at it.
                'prev_agreement_id' => $source['agreement']?->agreement_id,
                'parent_agreement_id' => $source['agreement']?->agreement_id,

                // Company is the one field the user can't change on the form.
                'cpny_id' => $source['cpny_id'],

                'business_id' => $request->business_id,
                'business_name' => $request->business_name,
                'tenant_no' => $request->tenant_no,
                'trade_name' => $request->trade_name,
                'property_cd' => $request->property_cd,
                'floor_id' => $request->floor_id,
                'unit_id' => $request->unit_id,
                'business_address' => $request->business_address,
                'pic_penyewa' => $request->pic_penyewa,
                'pic_phonenumber_penyewa' => $request->pic_phonenumber_penyewa,
                'pic_email_penyewa' => $request->pic_email_penyewa,
                'no_psm_or_addendum' => $request->no_psm_or_addendum,
                'pic_legal' => TrAgreement::joinPicList((array) $request->pic_legal),
                'pic_leasing' => TrAgreement::joinPicList((array) $request->pic_leasing),

                // Same landing step as Agreement FU: a freshly saved agreement
                // is Active (the Active tab lists agreement_step_id = ACTIVE).
                'agreement_step_id' => self::STEP_ACTIVE,
                'agreement_step_order' => 1,
                'agreement_step_created_user' => $username,
                'agreement_step_created_at' => now(),

                'status' => 'P',
                'created_user' => $username,
            ]);

            TrAgreementActivity::create([
                'agreement_id' => $agreement->agreement_id,
                'cpny_id' => $agreement->cpny_id,
                'agreement_activity_type' => self::ACTIVITY_CREATE_PEMBUATAN,
                'agreement_step_id' => self::STEP_ACTIVE,
                'agreement_step_order' => 1,
                'response_date' => now(),
                'response_summary' => $kind['docs'] ? 'Agreement Created (Pembuatan)' : 'Agreement Created ('.$kind['label'].')',
                'response_descr' => $agreement->business_name,
                'status_pekerjaan' => self::STEP_ACTIVE,
                'status' => 'A',
                'created_by' => $username,
            ]);

            // One checklist row per master document, whether or not the user
            // touched it, so the agreement always carries the full list.
            $this->checklistDocuments()
                ->each(function (MsAgreementDocument $doc) use ($agreement, $submittedDocs, $username) {
                    $input = $submittedDocs[$doc->agreementdocument_id] ?? [];

                    TrAgreementDocument::create([
                        'agreement_id' => $agreement->agreement_id,
                        'cpny_id' => $agreement->cpny_id,
                        'agreementdocument_id' => $doc->agreementdocument_id,
                        'agreementdocument_descr' => $doc->agreementdocument_descr,
                        // Required is the master's default; the user only ticks
                        // which documents the tenant already has.
                        'agreementdocument_required' => (bool) $doc->agreementdocument_required,
                        'agreementdocument_received' => (bool) ($input['received'] ?? false),
                        'agreementdocument_received_at' => ! empty($input['received']) ? now() : null,
                        'agreementdocument_note' => $input['note'] ?? null,
                        'status' => 'A',
                        'created_by' => $username,
                    ]);
                });

            // A PSM / OLA job row is now being worked: drop it off the pending list.
            // Addendums leave the IFCA job alone (the contract can have both).
            if ($kind['docs'] && $job) {
                StagingContractAgreement::query()
                    ->where('id', $job->id)
                    ->where('status', 'A')
                    ->update([
                        'status' => 'C',
                        'updated_by' => $username,
                        'updated_at' => now(),
                    ]);
            }

            DB::connection('pgsql5')->commit();
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'Failed to create agreement.',
            ], 500);
        }

        $this->notifyPsmOla($agreement, 'created', $username);

        return response()->json([
            'success' => true,
            'message' => "Agreement {$agreement->agreement_id} created.",
            // Fresh tab badges so the page can update without a reload.
            'counts' => $this->tabCounts(),
        ]);
    }

    // Same visibility as the Active list, so an eid can't be used to reach
    // an agreement the user couldn't see in the table.
    // View-only lookup: Active or Completed, with the same visibility.
    protected function viewableAgreementOrFail(string $eid): TrAgreement
    {
        $id = Hashids::decode($eid)[0] ?? null;

        abort_if(! $id, 404);

        return $this->activeQuery([self::STEP_ACTIVE, self::STEP_COMPLETED, self::STEP_CANCELLED])->findOrFail($id);
    }

    // Active agreement the current user may change: its creator or a PIC Legal.
    protected function updatableAgreementOrFail(string $eid): TrAgreement
    {
        $agreement = $this->activeAgreementOrFail($eid);

        abort_unless($agreement->canBeUpdatedBy((string) auth()->user()->username), 403, 'Only the creator or PIC Legal can change this agreement.');

        return $agreement;
    }

    protected function activeAgreementOrFail(string $eid): TrAgreement
    {
        $id = Hashids::decode($eid)[0] ?? null;

        abort_if(! $id, 404);

        return $this->activeQuery()->findOrFail($id);
    }

    // Read-only modal body for /legal-new-agreement/{eid}.
    public function viewPsmOla(string $eid)
    {
        $agreement = $this->viewableAgreementOrFail($eid);
        $completed = $agreement->agreement_step_id === self::STEP_COMPLETED;
        $cancelled = $agreement->agreement_step_id === self::STEP_CANCELLED;

        $company = MsCompany::query()->where('cpny_id', $agreement->cpny_id)->first(['cpny_id', 'cpny_name']);

        $kind = $this->kind();

        return view('pages.legal-new-agreement.psm-ola-agreement-form', [
            'kind' => $kind,
            'psmOlaNo' => $this->psmOlaNoFor($agreement),
            'related' => $this->relatedFor($agreement),
            'eid' => $eid,
            'agreement' => $agreement,
            'companyName' => $company?->cpny_name ?? $agreement->cpny_id,
            'documents' => $kind['docs']
                ? TrAgreementDocument::query()
                    ->where('agreement_id', $agreement->agreement_id)
                    ->whereNull('deleted_at')
                    ->orderBy('id')
                    ->get()
                : collect(),
            // Only the creator can change an agreement, and only while it is Active;
            // a completed one is read-only.
            'completed' => $completed,
            'cancelled' => $cancelled,
            'canUpdateDocs' => ! $completed && ! $cancelled && $agreement->canBeUpdatedBy((string) auth()->user()->username),
            'canManage' => $agreement->canBeUpdatedBy((string) auth()->user()->username),
            'processes' => $this->processesFor($agreement),
            'picLegalNames' => array_values($this->picLabels($agreement->picLegalList())),
            'picLeasingNames' => array_values($this->picLabels($agreement->picLeasingList())),
            'timeline' => $this->timelineFor($agreement),
            'attachments' => app(LegalAgreementController::class)->agreementAttachments($agreement),
            'propertyTypes' => self::PROPERTY_TYPES,
        ]);
    }

    /**
     * Oldest first, with an icon/colour per activity kind for the view modal.
     */
    protected function timelineFor(TrAgreement $agreement): array
    {
        return TrAgreementActivity::query()
            ->where('agreement_id', $agreement->agreement_id)
            // Create is the first row of the process table, not a log entry. Process
            // steps are one row each (kept current in place) and show here as-is;
            // the old separate PROCESS_UPDATE log rows are superseded by them.
            ->where('agreement_activity_type', '!=', self::ACTIVITY_CREATE_PEMBUATAN)
            ->where('agreement_activity_type', '!=', self::ACTIVITY_PROCESS)
            ->whereNull('deleted_at')
            ->orderBy('response_date')
            ->orderBy('id')
            ->get()
            ->map(function ($a) {
                if (str_starts_with($a->agreement_activity_type, self::PROCESS_PREFIX)) {
                    $fmt = fn ($d) => $d ? $d->format('D, d M Y') : null;
                    $parts = array_filter([
                        $fmt($a->working_start_date) ? 'Out: '.$fmt($a->working_start_date) : null,
                        $fmt($a->working_end_date) ? 'In: '.$fmt($a->working_end_date) : null,
                        $a->response_descr,
                    ]);

                    return [
                        'icon' => 'fa-route',
                        'color' => 'bg-amber-500',
                        'title' => $a->response_summary,
                        'descr' => implode("\n", $parts),
                        'by' => $a->updated_by ?: ($a->created_by ?: 'System'),
                        'at' => $a->response_date ? $a->response_date->format('Y-m-d H:i') : '-',
                    ];
                }

                $kind = match ($a->agreement_activity_type) {
                    self::ACTIVITY_CREATE_PEMBUATAN => ['fa-plus', 'bg-blue-500'],
                    self::ACTIVITY_UPDATE_PEMBUATAN => ['fa-pen', 'bg-indigo-500'],
                    self::ACTIVITY_ATTACHMENT => ['fa-paperclip', 'bg-teal-500'],
                    self::ACTIVITY_DOCUMENTS => ['fa-list-check', 'bg-emerald-500'],
                    self::ACTIVITY_PROCESS => ['fa-route', 'bg-amber-500'],
                    self::ACTIVITY_COMPLETE => ['fa-circle-check', 'bg-emerald-600'],
                    default => ['fa-bolt', 'bg-slate-500'],
                };

                return [
                    'icon' => $kind[0],
                    'color' => $kind[1],
                    'title' => $a->response_summary ?: 'Agreement Activity',
                    'descr' => $a->response_descr,
                    'by' => $a->created_by ?: 'System',
                    'at' => $a->response_date ? $a->response_date->format('Y-m-d H:i') : '-',
                ];
            })
            ->all();
    }

    /**
     * The process sheet (like the paper routing form), one row per step, all in
     * tr_agreement_activity. response_summary is the step name and response_date
     * is when the creator last updated that step.
     *  - MAIN steps (Create, Cetak) have no dates of their own: the step is
     *    done once its row exists, and response_date is the date shown. Create
     *    is the agreement's own creation activity (always done, read-only);
     *    Cetak gets a 'PROCESS:PRINT' row when ticked done.
     *  - ROUTING steps have an Out and an In date the creator picks
     *    (working_start_date = OUT, working_end_date = IN) in their own
     *    'PROCESS:<id>' row.
     * Notes live in response_descr (the Create row's holds the business name,
     * so Create has none).
     */
    protected function processesFor(TrAgreement $agreement)
    {
        $activities = TrAgreementActivity::query()
            ->where('agreement_id', $agreement->agreement_id)
            ->whereNull('deleted_at')
            ->where(fn ($q) => $q
                ->where('agreement_activity_type', 'like', self::PROCESS_PREFIX.'%')
                ->orWhere('agreement_activity_type', self::ACTIVITY_CREATE_PEMBUATAN))
            ->orderBy('id')
            ->get()
            ->keyBy(fn ($a) => $a->agreement_activity_type);

        return collect(self::PROCESS_STEPS)->map(function ($step, $id) use ($activities) {
            $row = $activities->get($this->processType($id));
            $main = $step['group'] === 'MAIN';

            return (object) [
                'process_id' => $id,
                'descr' => $step['descr'],
                'group' => $step['group'],
                'main' => $main,
                'has_in' => $step['has_in'],
                'has_note' => $id !== 'CREATE',
                'editable' => $id !== 'CREATE',
                'done' => (bool) $row,
                'date' => $main ? $row?->response_date : null,
                'date_in' => $main ? null : $row?->working_end_date,
                'date_out' => $main ? null : $row?->working_start_date,
                'note' => $id === 'CREATE' ? null : $row?->response_descr,
            ];
        })->values();
    }

    // Activity type a step is stored under.
    protected function processType(string $id): string
    {
        return $id === 'CREATE' ? self::ACTIVITY_CREATE_PEMBUATAN : self::PROCESS_PREFIX.$id;
    }

    /**
     * Creator-only: save the process sheet (done / dates + notes per step).
     */
    public function updatePsmOlaProcess(Request $request, string $eid)
    {
        $agreement = $this->activeAgreementOrFail($eid);
        $username = $request->user()->username ?? 'system';

        if (! $agreement->canBeUpdatedBy($username)) {
            return response()->json(['success' => false, 'message' => 'Only the creator or PIC Legal can update the process.'], 403);
        }

        $request->validate([
            'process' => 'required|array',
            'process.*.done' => 'nullable|boolean',
            'process.*.date_in' => 'nullable|date',
            'process.*.date_out' => 'nullable|date',
            'process.*.note' => 'nullable|string|max:2000',
        ]);

        $submitted = (array) $request->input('process', []);

        if (array_diff(array_keys($submitted), array_keys(self::PROCESS_STEPS))) {
            return response()->json(['success' => false, 'message' => 'Unknown process step.'], 422);
        }

        $changed = [];

        DB::connection('pgsql5')->beginTransaction();

        try {
            foreach ($submitted as $id => $input) {
                // Create is the agreement's own creation: never edited here.
                if ($id === 'CREATE') {
                    continue;
                }

                $step = self::PROCESS_STEPS[$id];
                $main = $step['group'] === 'MAIN';
                $note = filled($input['note'] ?? null) ? $input['note'] : null;

                $done = ! empty($input['done']);
                $in = ! $main && $step['has_in'] && ! empty($input['date_in']) ? Carbon::parse($input['date_in'])->startOfDay() : null;
                $out = ! $main && ! empty($input['date_out']) ? Carbon::parse($input['date_out'])->startOfDay() : null;

                // A single-date step keeps a note only while it is done.
                if ($main && $note && ! $done) {
                    DB::connection('pgsql5')->rollBack();

                    return response()->json(['success' => false, 'message' => "Tick {$step['descr']} as done before adding a note."], 422);
                }

                $row = TrAgreementActivity::query()
                    ->where('agreement_id', $agreement->agreement_id)
                    ->where('agreement_activity_type', $this->processType($id))
                    ->orderByRaw('deleted_at is not null')
                    ->orderBy('id')
                    ->first();

                // A step that was cleared earlier keeps its (retired) row: bring it back
                // instead of inserting a second one, so a step is always one row.
                $retired = $row && $row->deleted_at ? $row : null;
                $row = $retired ? null : $row;

                $empty = $main ? ! $done : (! $in && ! $out);

                // Nothing entered and nothing saved: don't create an empty row.
                if (! $row && $empty && ! $note) {
                    continue;
                }

                if ($row && $empty && ! $note) {
                    // Everything cleared: retire the row.
                    $row->update(['deleted_by' => $username, 'deleted_at' => now()]);
                    $changed[] = $step['descr'];

                    continue;
                }

                $fmt = fn ($d) => optional($d)->format('Y-m-d');

                if ($row
                    && $fmt($row->working_start_date) === $fmt($out)
                    && $fmt($row->working_end_date) === $fmt($in)
                    && (string) $row->response_descr === (string) $note) {
                    continue;
                }

                // response_date = when the creator updated this step.
                $attrs = [
                    'working_start_date' => $out,
                    'working_end_date' => $in,
                    'response_descr' => $note,
                    'response_date' => now(),
                ];

                if ($row) {
                    $row->update($attrs + ['updated_by' => $username]);
                } elseif ($retired) {
                    $retired->update($attrs + ['updated_by' => $username, 'deleted_by' => null, 'deleted_at' => null]);
                } else {
                    TrAgreementActivity::create($attrs + [
                        'agreement_id' => $agreement->agreement_id,
                        'cpny_id' => $agreement->cpny_id,
                        'agreement_activity_type' => $this->processType($id),
                        'agreement_step_id' => $agreement->agreement_step_id,
                        'agreement_step_order' => $agreement->agreement_step_order,
                        'response_summary' => $step['descr'],
                        'status_pekerjaan' => $agreement->agreement_step_id,
                        'status' => 'A',
                        'created_by' => $username,
                    ]);
                }

                $changed[] = $step['descr'];
            }

            DB::connection('pgsql5')->commit();
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'Failed to update process.',
            ], 500);
        }

        return response()->json(['success' => true, 'message' => $changed ? 'Process updated.' : 'No changes.']);
    }

    /**
     * Creator-only: mark the agreement complete. Same transition as Agreement FU
     * (step COMPLETED, status C), which also takes it off the Active list.
     */
    public function completePsmOla(Request $request, string $eid)
    {
        $agreement = $this->activeAgreementOrFail($eid);
        $username = $request->user()->username ?? 'system';

        if (! $agreement->canBeUpdatedBy($username)) {
            return response()->json(['success' => false, 'message' => 'Only the creator or PIC Legal can complete the agreement.'], 403);
        }

        DB::connection('pgsql5')->beginTransaction();

        try {
            $agreement->update([
                'agreement_step_id' => self::STEP_COMPLETED,
                'agreement_step_order' => $agreement->agreement_step_order + 1,
                'agreement_step_created_user' => $username,
                'agreement_step_created_at' => now(),
                'status' => 'C',
                'updated_user' => $username,
            ]);

            TrAgreementActivity::create([
                'agreement_id' => $agreement->agreement_id,
                'cpny_id' => $agreement->cpny_id,
                'agreement_activity_type' => self::ACTIVITY_COMPLETE,
                'agreement_step_id' => self::STEP_COMPLETED,
                'agreement_step_order' => $agreement->agreement_step_order,
                'response_date' => now(),
                'response_summary' => 'Agreement Completed',
                'response_descr' => $agreement->business_name,
                'status_pekerjaan' => self::STEP_COMPLETED,
                'status' => 'A',
                'created_by' => $username,
            ]);

            DB::connection('pgsql5')->commit();
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'Failed to complete agreement.',
            ], 500);
        }

        $this->notifyPsmOla($agreement, 'completed', $username);

        return response()->json([
            'success' => true,
            'message' => "Agreement {$agreement->agreement_id} completed.",
            'counts' => $this->tabCounts(),
        ]);
    }

    /**
     * Cancel an Active agreement (creator / PIC Legal, with a reason). It leaves every
     * list but stays in the database and its activity log. A PSM / OLA gives its
     * staging job back, so the job can be picked up again.
     */
    public function cancelPsmOla(Request $request, string $eid)
    {
        $agreement = $this->activeAgreementOrFail($eid);
        $username = $request->user()->username ?? 'system';

        if (! $agreement->canBeUpdatedBy($username)) {
            return response()->json(['success' => false, 'message' => 'Only the creator or PIC Legal can cancel the agreement.'], 403);
        }

        $request->validate(['reason' => 'required|string|max:500']);

        $kind = $this->kind();

        DB::connection('pgsql5')->beginTransaction();

        try {
            $agreement->update([
                'agreement_step_id' => self::STEP_CANCELLED,
                'agreement_step_order' => $agreement->agreement_step_order + 1,
                'agreement_step_created_user' => $username,
                'agreement_step_created_at' => now(),
                'status' => 'X',
                'updated_user' => $username,
            ]);

            $this->logActivity($agreement, self::ACTIVITY_CANCEL, self::STEP_CANCELLED, 'Agreement Cancelled', $request->reason, $username);

            // The job this PSM / OLA was made from (matched on the tenant + unit it copied) is pending again.
            if ($kind['docs']) {
                $jobs = StagingContractAgreement::query()
                    ->whereNull('deleted_at')
                    ->where('status', 'C')
                    ->where('cpny_id', $agreement->cpny_id)
                    ->where('business_id', $agreement->business_id)
                    ->where('tenant_no', $agreement->tenant_no)
                    ->where('lot_no', $agreement->unit_id)
                    ->get();

                if ($jobs->count() === 1) {
                    $jobs->first()->update(['status' => 'A', 'updated_by' => $username, 'updated_at' => now()]);
                }
            }

            DB::connection('pgsql5')->commit();
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'Failed to cancel agreement.',
            ], 500);
        }

        $this->notifyPsmOla($agreement, 'cancelled', $username, $request->reason);

        return response()->json([
            'success' => true,
            'message' => "Agreement {$agreement->agreement_id} cancelled.",
            'counts' => $this->tabCounts(),
        ]);
    }

    // Put a Completed agreement back to Active (creator / PIC Legal).
    public function reopenPsmOla(Request $request, string $eid)
    {
        $id = Hashids::decode($eid)[0] ?? null;

        abort_if(! $id, 404);

        $agreement = $this->activeQuery([self::STEP_COMPLETED, self::STEP_CANCELLED])->findOrFail($id);
        $username = $request->user()->username ?? 'system';
        $wasCancelled = $agreement->agreement_step_id === self::STEP_CANCELLED;

        if (! $agreement->canBeUpdatedBy($username)) {
            return response()->json(['success' => false, 'message' => 'Only the creator or PIC Legal can reopen the agreement.'], 403);
        }

        // Coming back to Active must not collide with what was raised meanwhile.
        $dupe = Request::create('/', 'POST', [
            'no_psm_or_addendum' => $agreement->no_psm_or_addendum,
            'tenant_no' => $agreement->tenant_no,
            'unit_id' => $agreement->unit_id,
        ]);

        if ($message = $this->duplicateMessage($dupe, $agreement, (string) $agreement->cpny_id)) {
            return response()->json(['success' => false, 'message' => $message], 422);
        }
        DB::connection('pgsql5')->beginTransaction();

        try {
            $agreement->update([
                'agreement_step_id' => self::STEP_ACTIVE,
                'agreement_step_order' => $agreement->agreement_step_order + 1,
                'agreement_step_created_user' => $username,
                'agreement_step_created_at' => now(),
                'status' => 'P',
                'updated_user' => $username,
            ]);

            $this->logActivity($agreement, self::ACTIVITY_REOPEN, self::STEP_ACTIVE, 'Agreement Reopened', $agreement->business_name, $username);

            // A cancelled PSM / OLA gave its staging job back: take it again.
            if ($wasCancelled && $this->kind()['docs']) {
                StagingContractAgreement::query()
                    ->whereNull('deleted_at')
                    ->where('status', 'A')
                    ->where('cpny_id', $agreement->cpny_id)
                    ->where('business_id', $agreement->business_id)
                    ->where('tenant_no', $agreement->tenant_no)
                    ->where('lot_no', $agreement->unit_id)
                    ->update(['status' => 'C', 'updated_by' => $username, 'updated_at' => now()]);
            }
            DB::connection('pgsql5')->commit();
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'Failed to reopen agreement.',
            ], 500);
        }

        $this->notifyPsmOla($agreement, 'reopened', $username);

        return response()->json([
            'success' => true,
            'message' => "Agreement {$agreement->agreement_id} reopened.",
            'counts' => $this->tabCounts(),
        ]);
    }

    // Remove an attachment from the list (creator / PIC Legal). The file itself stays in storage.
    public function deletePsmOlaAttachment(Request $request, string $eid)
    {
        $agreement = $this->updatableAgreementOrFail($eid);
        $username = $request->user()->username ?? 'system';

        $request->validate(['attachment_id' => 'required|integer']);

        $row = \App\Models\TrAgreementAttachment::query()
            ->where('agreement_id', $agreement->agreement_id)
            ->where('status', 'A')
            ->findOrFail($request->attachment_id);

        $row->update(['status' => 'X', 'deleted_by' => $username, 'deleted_at' => now()]);

        $this->logActivity($agreement, self::ACTIVITY_ATTACHMENT, $agreement->agreement_step_id, 'Attachment Deleted', $row->attachment_name, $username);

        return response()->json(['success' => true, 'message' => 'Attachment deleted.']);
    }

    protected function logActivity(TrAgreement $agreement, string $type, string $step, string $summary, ?string $descr, string $username): void
    {
        TrAgreementActivity::create([
            'agreement_id' => $agreement->agreement_id,
            'cpny_id' => $agreement->cpny_id,
            'agreement_activity_type' => $type,
            'agreement_step_id' => $step,
            'agreement_step_order' => $agreement->agreement_step_order,
            'response_date' => now(),
            'response_summary' => $summary,
            'response_descr' => $descr,
            'status_pekerjaan' => $step,
            'status' => 'A',
            'created_by' => $username,
        ]);
    }

    // Fields whose edits are written to the activity log as "Label: old → new".
    protected function trackedValues(TrAgreement $agreement): array
    {
        $no = $this->kind()['key'] === 'addendum' ? 'No. Addendum' : 'No. PSM / Addendum';

        $labels = [
            'business_id' => 'Business ID', 'business_name' => 'Business Name', 'tenant_no' => 'Tenant No',
            'trade_name' => 'Trade Name', 'floor_id' => 'Floor', 'unit_id' => 'Unit', 'business_address' => 'Address',
            'pic_penyewa' => 'PIC Name', 'pic_phonenumber_penyewa' => 'PIC Phone', 'pic_email_penyewa' => 'Email',
            'no_psm_or_addendum' => $no,
            'pic_legal' => 'PIC Legal', 'pic_leasing' => 'PIC Leasing',
        ];

        $out = [];

        foreach ($labels as $field => $label) {
            $value = $agreement->{$field};
            $out[$label] = str_contains($field, '_date') ? substr((string) $value, 0, 10) : trim((string) $value);
        }

        return $out;
    }

    protected function changeSummary(array $before, array $after): string
    {
        $lines = [];

        foreach ($after as $label => $new) {
            if (($before[$label] ?? '') !== $new) {
                $lines[] = $label.': '.(($before[$label] ?? '') !== '' ? $before[$label] : '-').' → '.($new !== '' ? $new : '-');
            }
        }

        return implode("\n", $lines);
    }

    /**
     * One live agreement per number, and one active PSM / OLA per tenant + unit.
     * Cancelled agreements don't count.
     */
    protected function duplicateMessage(Request $request, ?TrAgreement $self, string $cpnyId): ?string
    {
        $kind = $this->kind();

        $base = TrAgreement::query()
            ->whereNull('deleted_at')
            ->whereIn('agreement_type', (array) $kind['type'])
            ->whereIn('agreement_step_id', [self::STEP_ACTIVE, self::STEP_COMPLETED]);

        if ($self) {
            $base->where('id', '!=', $self->id);
        }

        $no = trim((string) $request->no_psm_or_addendum);

        if ($no !== '') {
            $hit = (clone $base)->whereRaw('lower(no_psm_or_addendum) = ?', [mb_strtolower($no)])->first();

            if ($hit) {
                return "{$no} is already used by {$hit->agreement_id}.";
            }
        }

        $tenant = trim((string) $request->tenant_no);
        $unit = trim((string) $request->unit_id);

        if ($tenant !== '' && $unit !== '') {
            $hit = (clone $base)->where('agreement_step_id', self::STEP_ACTIVE)
                ->where('cpny_id', $cpnyId)->where('tenant_no', $tenant)->where('unit_id', $unit)->first();

            if ($hit) {
                return "An active {$kind['label']} ({$hit->agreement_id}) already exists for this tenant and unit.";
            }
        }

        return null;
    }
    // When a document was received: stamped on the untick->tick change, kept
    // while it stays ticked (so a note edit doesn't move it), cleared on untick.
    protected function receivedAt(TrAgreementDocument $doc, bool $received)
    {
        if (! $received) {
            return null;
        }

        return $doc->agreementdocument_received ? ($doc->agreementdocument_received_at ?? now()) : now();
    }

    /**
     * Creator-only: record documents received after the agreement was made
     * (tick/untick + note). Only the checklist rows change, not the tenant data.
     */
    public function updatePsmOlaDocuments(Request $request, string $eid)
    {
        $agreement = $this->activeAgreementOrFail($eid);
        $username = $request->user()->username ?? 'system';

        if (! $agreement->canBeUpdatedBy($username)) {
            return response()->json(['success' => false, 'message' => 'Only the creator or PIC Legal can update the documents.'], 403);
        }

        $request->validate([
            'documents' => 'required|array',
            'documents.*.received' => 'nullable|boolean',
            'documents.*.note' => 'nullable|string|max:2000',
        ]);

        $submitted = (array) $request->input('documents', []);
        $newlyReceived = [];

        DB::connection('pgsql5')->beginTransaction();

        try {
            TrAgreementDocument::query()
                ->where('agreement_id', $agreement->agreement_id)
                ->whereNull('deleted_at')
                ->get()
                ->each(function (TrAgreementDocument $doc) use ($submitted, $username, &$newlyReceived) {
                    if (! array_key_exists($doc->agreementdocument_id, $submitted)) {
                        return;
                    }

                    $input = $submitted[$doc->agreementdocument_id];
                    $received = (bool) ($input['received'] ?? false);
                    $note = $input['note'] ?? null;

                    if ($received && ! $doc->agreementdocument_received) {
                        $newlyReceived[] = $doc->agreementdocument_descr;
                    }

                    if ($received !== (bool) $doc->agreementdocument_received || $note !== $doc->agreementdocument_note) {
                        $doc->update([
                            'agreementdocument_received' => $received,
                            'agreementdocument_received_at' => $this->receivedAt($doc, $received),
                            'agreementdocument_note' => $note,
                            'updated_by' => $username,
                        ]);
                    }
                });

            TrAgreementActivity::create([
                'agreement_id' => $agreement->agreement_id,
                'cpny_id' => $agreement->cpny_id,
                'agreement_activity_type' => self::ACTIVITY_DOCUMENTS,
                'agreement_step_id' => $agreement->agreement_step_id,
                'agreement_step_order' => $agreement->agreement_step_order,
                'response_date' => now(),
                'response_summary' => 'Documents Updated',
                'response_descr' => $newlyReceived ? "Received:\n".implode("\n", $newlyReceived) : null,
                'status_pekerjaan' => $agreement->agreement_step_id,
                'status' => 'A',
                'created_by' => $username,
            ]);

            DB::connection('pgsql5')->commit();
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'Failed to update documents.',
            ], 500);
        }

        return response()->json(['success' => true, 'message' => 'Documents updated.']);
    }

    public function uploadPsmOlaAttachment(Request $request, string $eid)
    {
        $agreement = $this->updatableAgreementOrFail($eid);

        $request->validate([
            'attachments' => 'required|array|min:1',
            'attachments.*' => ['file', 'max:5120', 'mimes:jpg,jpeg,png,pdf,xlsx,xls,doc,docx'],
        ]);

        $username = $request->user()->username ?? 'system';
        $uploader = app(LegalAgreementController::class);
        $names = [];

        try {
            foreach ((array) $request->file('attachments') as $file) {
                $names[] = $file->getClientOriginalName();
                $uploader->uploadAgreementAttachment($agreement, $file, $username, $file->getClientOriginalName());
            }

            TrAgreementActivity::create([
                'agreement_id' => $agreement->agreement_id,
                'cpny_id' => $agreement->cpny_id,
                'agreement_activity_type' => self::ACTIVITY_ATTACHMENT,
                'agreement_step_id' => $agreement->agreement_step_id,
                'agreement_step_order' => $agreement->agreement_step_order,
                'response_date' => now(),
                'response_summary' => 'Attachment Uploaded',
                'response_descr' => implode("\n", $names),
                'status_pekerjaan' => $agreement->agreement_step_id,
                'status' => 'A',
                'created_by' => $username,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'Failed to upload attachment.',
            ], 500);
        }

        return response()->json(['success' => true, 'message' => count($names).' file(s) uploaded.']);
    }

    public function updatePsmOla(Request $request, string $eid)
    {
        $agreement = $this->updatableAgreementOrFail($eid);

        // Company and Property Type are intentionally not accepted here.
        $request->validate([
            'business_id' => 'required|integer',
            'business_name' => 'required|max:255',
            'tenant_no' => 'nullable|max:20',
            'trade_name' => 'nullable|max:255',
            'floor_id' => 'nullable|max:20',
            'unit_id' => 'nullable|max:20',
            'business_address' => 'nullable|max:500',
            'pic_penyewa' => 'nullable|max:255',
            'pic_phonenumber_penyewa' => 'nullable|max:50',
            'pic_email_penyewa' => 'nullable|email|max:255',

            'no_psm_or_addendum' => 'required|max:150',
            'pic_legal' => 'required|array|min:1',
            'pic_legal.*' => ['string', Rule::exists('pgsql2.sys_user_role', 'username')->where('role_id', self::ROLE_PIC_LEGAL)->where('status', 'A')],
            'pic_leasing' => 'required|array|min:1',
            'pic_leasing.*' => ['string', Rule::exists('pgsql2.sys_user_role', 'username')->where('role_id', self::ROLE_PIC_LEASING)->where('status', 'A')],

            'documents' => 'nullable|array',
            'documents.*.received' => 'nullable|boolean',
            'documents.*.note' => 'nullable|string|max:2000',
        ]);

        $username = $request->user()->username ?? 'system';
        $kind = $this->kind();
        $submittedDocs = $kind['docs'] ? (array) $request->input('documents', []) : [];

        if ($message = $this->duplicateMessage($request, $agreement, (string) $agreement->cpny_id)) {
            return response()->json(['success' => false, 'message' => $message], 422);
        }

        $before = $this->trackedValues($agreement);

        DB::connection('pgsql5')->beginTransaction();

        try {
            $agreement->update([
                'business_id' => $request->business_id,
                'business_name' => $request->business_name,
                'tenant_no' => $request->tenant_no,
                'trade_name' => $request->trade_name,
                'floor_id' => $request->floor_id,
                'unit_id' => $request->unit_id,
                'business_address' => $request->business_address,
                'pic_penyewa' => $request->pic_penyewa,
                'pic_phonenumber_penyewa' => $request->pic_phonenumber_penyewa,
                'pic_email_penyewa' => $request->pic_email_penyewa,
                'no_psm_or_addendum' => $request->no_psm_or_addendum,
                'pic_legal' => TrAgreement::joinPicList((array) $request->pic_legal),
                'pic_leasing' => TrAgreement::joinPicList((array) $request->pic_leasing),
                'updated_user' => $username,
            ]);

            $saved = TrAgreementDocument::query()
                ->where('agreement_id', $agreement->agreement_id)
                ->whereNull('deleted_at')
                ->get()
                ->keyBy('agreementdocument_id');

            $this->checklistDocuments()
                ->each(function (MsAgreementDocument $master) use ($agreement, $saved, $submittedDocs, $username) {
                    $input = $submittedDocs[$master->agreementdocument_id] ?? null;
                    $row = $saved->get($master->agreementdocument_id);

                    if ($row) {
                        // Rows the form didn't send are left alone.
                        if ($input !== null) {
                            $row->update([
                                'agreementdocument_received' => (bool) ($input['received'] ?? false),
                                'agreementdocument_received_at' => $this->receivedAt($row, (bool) ($input['received'] ?? false)),
                                'agreementdocument_note' => $input['note'] ?? null,
                                'updated_by' => $username,
                            ]);
                        }

                        return;
                    }

                    // A master document added after this agreement was created.
                    TrAgreementDocument::create([
                        'agreement_id' => $agreement->agreement_id,
                        'cpny_id' => $agreement->cpny_id,
                        'agreementdocument_id' => $master->agreementdocument_id,
                        'agreementdocument_descr' => $master->agreementdocument_descr,
                        'agreementdocument_required' => (bool) $master->agreementdocument_required,
                        'agreementdocument_received' => (bool) ($input['received'] ?? false),
                        'agreementdocument_received_at' => ! empty($input['received']) ? now() : null,
                        'agreementdocument_note' => $input['note'] ?? null,
                        'status' => 'A',
                        'created_by' => $username,
                    ]);
                });

            TrAgreementActivity::create([
                'agreement_id' => $agreement->agreement_id,
                'cpny_id' => $agreement->cpny_id,
                'agreement_activity_type' => self::ACTIVITY_UPDATE_PEMBUATAN,
                'agreement_step_id' => $agreement->agreement_step_id,
                'agreement_step_order' => $agreement->agreement_step_order,
                'response_date' => now(),
                'response_summary' => $kind['docs'] ? 'Agreement Updated (Pembuatan)' : 'Agreement Updated ('.$kind['label'].')',
                'response_descr' => $this->changeSummary($before, $this->trackedValues($agreement)) ?: $agreement->business_name,
                'status_pekerjaan' => $agreement->agreement_step_id,
                'status' => 'A',
                'created_by' => $username,
            ]);

            DB::connection('pgsql5')->commit();
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'Failed to update agreement.',
            ], 500);
        }

        $this->notifyPsmOla($agreement, 'updated', $username);

        return response()->json([
            'success' => true,
            'message' => "Agreement {$agreement->agreement_id} updated.",
        ]);
    }

    /**
     * Emails the creator and the PIC Legal(s) after a create / save. A mail
     * failure is logged and never fails the save, which is already committed.
     */
    protected function notifyPsmOla(TrAgreement $agreement, string $event, string $actor, ?string $note = null): void
    {
        $agreement->refresh();

        $usernames = collect([$agreement->created_user])
            ->merge($agreement->picLegalList())
            ->filter()
            ->unique(fn ($u) => strtolower($u));

        $users = User::query()->whereIn('username', $usernames->all())->get()->keyBy(fn ($u) => strtolower($u->username));
        $picNames = implode(', ', $this->picLabels($agreement->picLegalList()));

        $sent = [];

        foreach ($usernames as $username) {
            $user = $users->get(strtolower($username));
            $email = $user?->notification_email ?: $user?->email;

            if (! $email || isset($sent[strtolower($email)])) {
                continue;
            }

            $sent[strtolower($email)] = true;

            try {
                \Illuminate\Support\Facades\Mail::to($email)->send(new \App\Mail\PsmOlaAgreementMail($agreement, $event, $actor, $picNames, $note));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('PSM/OLA mail failed', [
                    'agreement_id' => $agreement->agreement_id,
                    'email' => $email,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    // username => "Name - Email" for the already-chosen PICs (select2 options).
    protected function picLabels(array $usernames): array
    {
        if (! $usernames) {
            return [];
        }

        $users = User::query()->whereIn('username', $usernames)->get(['username', 'name', 'email'])->keyBy('username');

        return collect($usernames)->mapWithKeys(function ($u) use ($users) {
            $row = $users->get($u);

            return [$u => $row ? ($row->email ? "{$row->name} - {$row->email}" : $row->name) : $u];
        })->all();
    }

    // Same PIC search Agreement FU uses (role / company scoped), under this page's own access.
    public function picSearch(Request $request)
    {
        return app(LegalAgreementController::class)->picSearch($request);
    }
    protected function pendingJobOrFail(string $eid): StagingContractAgreement
    {
        $id = Hashids::decode($eid)[0] ?? null;

        abort_if(! $id, 404);

        // Same pending rule as the Job List, so a job that has since been
        // picked up (or got a contract_no, or already has an active agreement) can't be created twice.
        return StagingContractAgreement::query()
            ->whereNull('deleted_at')
            ->where('status', 'A')
            ->withoutContractNo()
            ->withoutActiveAgreement((array) $this->kind()['type'])
            ->findOrFail($id);
    }

    /**
     * Next number for (doctype, year, month) from ms_autonbr, creating the
     * month's row on first use. The lock and the increment share one pgsql2
     * transaction so concurrent saves can't hand out the same number.
     */
    protected function nextAutonbr(string $doctype, int $year, string $month, string $username, string $descr): int
    {
        return DB::connection('pgsql2')->transaction(function () use ($doctype, $year, $month, $username, $descr) {
            $row = Autonbr::query()
                ->lockForUpdate()
                ->where('doctype', $doctype)
                ->where('year', $year)
                ->where('month', $month)
                ->first();

            if (! $row) {
                $row = Autonbr::create([
                    'doctype' => $doctype,
                    'doctype_descr' => $descr,
                    'year' => $year,
                    'month' => $month,
                    'number' => 0,
                    'status' => 'A',
                    'created_by' => $username,
                ]);
            }

            $row->number = ((int) $row->number) + 1;
            $row->updated_by = $username;
            $row->save();

            return (int) $row->number;
        });
    }

    // The Job List is the same staging query Agreement FU uses (not deleted,
    // status 'A', no contract_no), so both lists are served by the one
    // implementation in LegalAgreementController and can't drift apart.
    public function jobsJson(Request $request)
    {
        return app(LegalAgreementController::class)->jobsJson($request, (array) $this->kind()['type']);
    }

    public function jobsExport(Request $request)
    {
        return app(LegalAgreementController::class)->jobsExport($request, (array) $this->kind()['type']);
    }

    // Addendum is the same page as PSM / OLA (see kind()); its Jobs tab has two sources.
    public function addendum()
    {
        return $this->psmOla();
    }

    // PSM / OLA agreements (Active and Completed) an addendum can be started from.
    public function addendumPsmOlaJson(Request $request)
    {
        return $this->listJson($request, $this->psmOlaSourceQuery());
    }
    public function others()
    {
        return view('pages.legal-new-agreement.placeholder', [
            'title' => 'Others',
            'description' => 'Other agreement requests will be managed here.',
        ]);
    }
}
