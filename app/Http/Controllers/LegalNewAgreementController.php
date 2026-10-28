<?php

namespace App\Http\Controllers;

use App\Models\Autonbr;
use App\Models\MsAgreementCategory;
use App\Models\MsAgreementDocument;
use App\Models\MsCompany;
use App\Models\StagingContractAgreement;
use App\Models\TrAgreement;
use App\Models\TrAgreementActivity;
use App\Models\TrAgreementDocument;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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

    // Process-sheet rows in tr_agreement_activity: type = this prefix + the ms_agreement_category
    // id (type PROCESS). Pengumpulan Dokumen is the one the system adds itself; see processesFor().
    protected const PROCESS_PREFIX = 'PROCESS:';

    protected const PROCESS_DOC = 'DOC_COMPLETENESS';

    // "Process PSM / OLA": added when the agreement is Processed, Start = the day Process was clicked.
    protected const PROCESS_START = 'PROCESS';

    protected const STEP_ACTIVE = 'ACTIVE';

    protected const STEP_COMPLETED = 'COMPLETED';

    // Saved from the Review step with "Save as Draft": not Active yet, no email, editable until it is Processed.
    protected const STEP_DRAFT = 'DRAFT';

    // Roles whose holders can be picked as PIC Legal / PIC Leasing.
    protected const ROLE_PIC_LEGAL = 'LEGALACCESS';

    protected const ROLE_PIC_LEASING = 'LEASINGACCESS';

    protected const ACTIVITY_COMPLETE = 'COMPLETE_PEMBUATAN';

    protected const ACTIVITY_CANCEL = 'CANCEL_PEMBUATAN';

    protected const ACTIVITY_REOPEN = 'REOPEN_PEMBUATAN';

    protected const STEP_CANCELLED = 'CANCELLED';

    protected const PROPERTY_TYPES = ['OFF' => 'Office', 'MALL' => 'Mall', 'APT' => 'Apartment', 'HOTEL' => 'Hotel'];

    protected const TYPE_ADDENDUM = 'ADDENDUM';

    protected const TYPE_OTHERS = 'OTHERS';

    // Fields that come from the IFCA job (or the PSM / OLA) and are fixed once an agreement exists.
    protected const LOCKED_FIELDS = ['business_id', 'tenant_no', 'unit_id', 'property_cd'];

    /**
     * PSM / OLA and Addendum run on the same code. Which one a request is for
     * comes from its URL (/legal-new-agreement/addendum/...). Both carry the
     * document checklist ('checklist'); only PSM / OLA is tied to an IFCA job
     * ('docs'), while an Addendum can also be started from an existing PSM / OLA.
     * 'r' holds the route names the views need.
     */
    protected function kind(): array
    {
        $name = request()->is('legal-new-agreement/addendum*') ? 'addendum'
            : (request()->is('legal-new-agreement/others*') ? 'others' : null);

        if ($name) {
            $others = $name === 'others';
            $p = 'legal-new-agreement.'.$name;

            return [
                'key' => $name,
                'type' => $others ? self::TYPE_OTHERS : self::TYPE_ADDENDUM,
                'doctype' => $others ? 'NAO' : 'NAD',
                'label' => $others ? 'Others' : 'Addendum',
                'no_label' => $others ? 'No. Others' : 'No. Addendum',
                'date_label' => $others ? 'Others Date' : 'Addendum Date',
                'docs' => false,
                'checklist' => true,
                // Can also be started from an existing PSM / OLA (second Jobs source).
                'from_psm' => true,
                // Others only routes when asked: the "Route" button on its process sheet opens Routing
                // (no Cetak needed first); PSM / OLA and Addendum start Routing once Cetak is added.
                'routing' => true,
                'routing_optional' => $others,
                // An Addendum's follow-ups are FU_ADDENDUM; Others has none.
                'follow_up_types' => $others ? null : ['FU_ADDENDUM'],
                'page' => $p,
                'r' => [
                    'jobs_json' => $p.'.jobs.json',
                    'jobs_export' => $p.'.jobs.export',
                    'active_json' => $p.'.active.json',
                    'completed_json' => $p.'.completed.json',
                    'cancelled_json' => $p.'.cancelled.json',
                    'draft_json' => $p.'.draft.json',
                    'cancelled_export' => $p.'.cancelled.export',
                    'active_export' => $p.'.active.export',
                    'completed_export' => $p.'.completed.export',
                    'source_json' => $p.'.psm-ola.json',
                    'view' => $p.'.view',
                    'edit' => $p.'.edit',
                    'update' => $p.'.update',
                    'create' => $p.'.create',
                    'create_link' => $p.'.create-agreement',
                    'follow_up_json' => $others ? null : $p.'.follow-up.json',
                    'store' => $p.'.store',
                    'show' => $p.'.show',
                    'attachment' => $p.'.attachment',
                    'process' => $p.'.process',
                    'complete' => $p.'.complete',
                    'activate' => $p.'.activate',
                    'cancel' => $p.'.cancel',
                    'reopen' => $p.'.reopen',
                    'attachment_delete' => $p.'.attachment.delete',
                    'documents' => $p.'.documents',
                    'pic_search' => $p.'.pic-search',
                ],
            ];
        }

        return [
            'key' => 'psm-ola',
            'type' => self::PSM_OLA_TYPES,
            'doctype' => self::DOCTYPE,
            'label' => 'PSM / OLA',
            'no_label' => 'No. PSM / OLA',
            'date_label' => 'PSM / OLA Date',
            'docs' => true,
            'checklist' => true,
            'from_psm' => false,
            'routing' => true,
            'follow_up_types' => TrAgreement::FU_PARENT_TYPES,
            'page' => 'legal-new-agreement.psm-ola',
            'r' => [
                'jobs_json' => 'legal-new-agreement.jobs.json',
                'jobs_export' => 'legal-new-agreement.jobs.export',
                'active_json' => 'legal-new-agreement.active.json',
                'completed_json' => 'legal-new-agreement.completed.json',
                'cancelled_json' => 'legal-new-agreement.cancelled.json',
                'draft_json' => 'legal-new-agreement.draft.json',
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
                'activate' => 'legal-new-agreement.psm-ola.activate',
                'cancel' => 'legal-new-agreement.psm-ola.cancel',
                'reopen' => 'legal-new-agreement.psm-ola.reopen',
                'attachment_delete' => 'legal-new-agreement.psm-ola.attachment.delete',
                'create_link' => 'create-agreement',
                'follow_up_json' => 'legal-new-agreement.follow-up.json',
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
    // One that has moved on to a follow-up (FU_PSM / FU_OLA, in any step) is still a valid parent.
    protected function psmOlaSourceQuery()
    {
        $type = $this->kind()['type'];

        return $this->activeQuery(
            [self::STEP_ACTIVE, self::STEP_COMPLETED, 'HOLD', 'ESCALATED'],
            [...self::PSM_OLA_TYPES, ...TrAgreement::FU_PARENT_TYPES]
        )
            ->whereNotExists(function ($sub) use ($type) {
                $sub->selectRaw('1')
                    ->from('tr_agreement as ad')
                    ->whereNull('ad.deleted_at')
                    ->where('ad.agreement_step_id', self::STEP_ACTIVE)
                    ->where('ad.agreement_type', $type)
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
            'draft' => $this->activeQuery(self::STEP_DRAFT)->count(),
        ];

        // PSM / OLAs that moved on to Agreement FU leave Active and are listed under Follow Up.
        if ($kind['follow_up_types']) {
            $counts['follow_up'] = $this->followUpQuery()->count();
        }

        if ($kind['from_psm']) {
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
        // Old links (emails, bookmarks) to a PSM / OLA that has since become a follow-up.
        $id = Hashids::decode($eid)[0] ?? null;

        if ($id && TrAgreement::query()->whereKey($id)->whereIn('agreement_type', TrAgreement::FU_TYPES)->exists()) {
            return redirect('/show-legal-agreement/'.$eid);
        }

        $agreement = $this->viewableAgreementOrFail($eid);

        return $this->psmOla(null, $eid, ['COMPLETED' => 'completed', 'CANCELLED' => 'cancelled', 'DRAFT' => 'draft'][$agreement->agreement_step_id] ?? 'active');
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

    // PSM / OLAs that became a follow-up: their type was converted in place to FU_PSM / FU_OLA, in any step.
    protected function followUpQuery()
    {
        return $this->activeQuery(
            [self::STEP_ACTIVE, 'HOLD', 'ESCALATED', self::STEP_COMPLETED, self::STEP_CANCELLED],
            $this->kind()['follow_up_types'] ?: ['NONE']
        );
    }

    public function followUpJson(Request $request)
    {
        return $this->listJson($request, $this->followUpQuery());
    }

    public function draftJson(Request $request)
    {
        return $this->listJson($request, $this->activeQuery(self::STEP_DRAFT));
    }

    // Company / Type / search filters shared by the list and its Excel export.
    protected function applyListFilters(Request $request, $query): void
    {
        if ($request->filled('cpny_id')) {
            $query->where('cpny_id', $request->cpny_id);
        }

        if (is_string($request->property_cd) && array_key_exists($request->property_cd, self::PROPERTY_TYPES)) {
            $query->where('property_cd', $request->property_cd);
        }

        // Guarded to strings only — DataTables' own search[value] is an array
        // and is applied by DataTables::of() itself.
        if (is_string($request->search) && $request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('agreement_id', 'ilike', "%{$search}%")
                    ->orWhere('no_psm_or_addendum', 'ilike', "%{$search}%")
                    ->orWhere('business_name', 'ilike', "%{$search}%")
                    ->orWhere('trade_name', 'ilike', "%{$search}%")
                    ->orWhere('floor_loi', 'ilike', "%{$search}%")
                    ->orWhere('unit_loi', 'ilike', "%{$search}%")
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
            $a->floor_loi,
            $a->unit_loi,
            $a->no_psm_or_addendum,
            substr((string) $a->psm_or_addendum_date, 0, 10) ?: null,
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
            'isDraft' => false,
            'actionUrl' => route($kind['r']['store'], $eid, false).($source['agreement'] ? '?src=psm' : ''),
            'subtitle' => $source['subtitle'],
            'psmOlaNo' => $source['agreement']?->no_psm_or_addendum,
            'psmOlaDate' => substr((string) $source['agreement']?->psm_or_addendum_date, 0, 10) ?: null,
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

        if ($this->kind()['from_psm']) {
            if (! $agreement->prev_agreement_id) {
                return [];
            }

            // The parent may have become a follow-up (any step); that one lives on the Agreement FU page.
            $rows = $this->activeQuery([...$steps, 'HOLD', 'ESCALATED'], [...self::PSM_OLA_TYPES, ...TrAgreement::FU_PARENT_TYPES])
                ->where('agreement_id', $agreement->prev_agreement_id)->get();
            $type = 'PSM / OLA';
            $path = '/legal-new-agreement/';
        } else {
            $rows = $this->activeQuery([...$steps, 'HOLD', 'ESCALATED'], [self::TYPE_ADDENDUM, self::TYPE_OTHERS, 'FU_ADDENDUM'])
                ->where(fn ($q) => $q->where('prev_agreement_id', $agreement->agreement_id)->orWhere('parent_agreement_id', $agreement->agreement_id))
                ->orderBy('agreement_date')->orderBy('id')
                ->get();
            $type = null;
            $path = null;
        }

        return $rows->map(function (TrAgreement $r) use ($type, $path) {
            $followUp = in_array($r->agreement_type, TrAgreement::FU_TYPES, true);

            // A PSM / OLA's related rows are its addendums and Others (each on its own page).
            if (! $type) {
                $isOthers = $r->agreement_type === self::TYPE_OTHERS;
                $type = $isOthers ? 'Others' : 'Addendum';
                $path = '/legal-new-agreement/'.($isOthers ? 'others/' : 'addendum/');
            }

            return [
                'type' => $followUp ? 'Follow Up' : $type,
                'agreement_id' => $r->agreement_id,
                'number' => $r->no_psm_or_addendum,
                'date' => optional($r->agreement_date)->format('Y-m-d'),
                'created_by' => $r->created_user,
                'completed' => $r->agreement_step_id === self::STEP_COMPLETED,
                'url' => $followUp ? '/show-legal-agreement/'.Hashids::encode($r->id) : $path.Hashids::encode($r->id),
            ];
        })->all();
    }

    // An addendum made from a PSM / OLA shows that PSM / OLA's number and date.
    protected function psmOlaNoFor(TrAgreement $agreement): ?string
    {
        return $this->psmOlaParentFor($agreement)?->no_psm_or_addendum;
    }

    protected function psmOlaDateFor(TrAgreement $agreement): ?string
    {
        return substr((string) $this->psmOlaParentFor($agreement)?->psm_or_addendum_date, 0, 10) ?: null;
    }

    protected function psmOlaParentFor(TrAgreement $agreement): ?TrAgreement
    {
        if (! $this->kind()['from_psm'] || ! $agreement->prev_agreement_id) {
            return null;
        }

        return TrAgreement::query()
            ->where('agreement_id', $agreement->prev_agreement_id)
            // Still the parent's number once it has moved on to a follow-up (FU_PSM / FU_OLA).
            ->whereIn('agreement_type', [...self::PSM_OLA_TYPES, ...TrAgreement::FU_PARENT_TYPES])
            ->first(['agreement_id', 'no_psm_or_addendum', 'psm_or_addendum_date']);
    }

    // The master checklist; Addendum has none.
    /**
     * Free-text rows the user added on the checklist for this agreement only. They live in
     * tr_agreement_document under a generated CUSTOM_ id and never touch the master.
     * Rows sent with an id are updated, rows without one are created, and saved custom rows
     * the form no longer sends were removed by the user, so they are soft-deleted.
     */
    protected function syncCustomDocuments(TrAgreement $agreement, array $rows, string $username): void
    {
        $existing = TrAgreementDocument::query()
            ->where('agreement_id', $agreement->agreement_id)
            ->where('agreementdocument_id', 'like', 'CUSTOM\_%')
            ->whereNull('deleted_at')
            ->get()
            ->keyBy('agreementdocument_id');

        $kept = [];

        foreach ($rows as $row) {
            $received = (bool) ($row['received'] ?? false);
            $row['name'] = trim((string) $row['name']);
            $doc = $existing->get($row['id'] ?? null);

            if ($doc) {
                $kept[] = $doc->agreementdocument_id;
                $doc->update([
                    'agreementdocument_descr' => $row['name'],
                    'agreementdocument_received' => $received,
                    'agreementdocument_received_at' => $this->receivedAt($doc, $received),
                    'agreementdocument_note' => $row['note'] ?? null,
                    'updated_by' => $username,
                ]);

                continue;
            }

            TrAgreementDocument::create([
                'agreement_id' => $agreement->agreement_id,
                'cpny_id' => $agreement->cpny_id,
                'agreementdocument_id' => 'CUSTOM_'.strtoupper(Str::random(10)),
                'agreementdocument_descr' => $row['name'],
                'agreementdocument_required' => false,
                'agreementdocument_received' => $received,
                'agreementdocument_received_at' => $received ? now() : null,
                'agreementdocument_note' => $row['note'] ?? null,
                'status' => 'A',
                'created_by' => $username,
            ]);
        }

        $existing->reject(fn (TrAgreementDocument $doc) => in_array($doc->agreementdocument_id, $kept, true))->each(fn (TrAgreementDocument $doc) => $doc->update([
            'deleted_at' => now(),
            'deleted_by' => $username,
        ]));
    }

    protected function checklistDocuments()
    {
        if (! $this->kind()['checklist']) {
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
        if ($this->kind()['from_psm'] && $request->query('src') === 'psm') {
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
                    'floor_loi', 'unit_loi', 'business_address', 'pic_penyewa', 'pic_phonenumber_penyewa', 'pic_email_penyewa',
                ]) + ['no_psm_or_addendum' => null, 'psm_or_addendum_date' => null],
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
                'floor_loi' => null,
                'unit_loi' => null,
                'business_address' => $job->mailing_addr,
                'pic_penyewa' => null,
                'pic_phonenumber_penyewa' => null,
                'pic_email_penyewa' => trim((string) $job->email_addr) ?: trim((string) $job->email_addr2),
                'no_psm_or_addendum' => null,
                'psm_or_addendum_date' => null,
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
        $agreement = $this->updatableAgreementOrFail($eid, true);

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
            'isDraft' => $agreement->agreement_step_id === self::STEP_DRAFT,
            'actionUrl' => route($kind['r']['update'], $eid, false),
            'subtitle' => $agreement->agreement_id.' · '.($agreement->trade_name ?: $agreement->business_name),
            'psmOlaNo' => $this->psmOlaNoFor($agreement),
            'psmOlaDate' => $this->psmOlaDateFor($agreement),
            'fromPsmOla' => (bool) $agreement->prev_agreement_id,
            'values' => $agreement->only([
                'business_id', 'tenant_no', 'trade_name', 'business_name', 'property_cd', 'floor_id', 'unit_id',
                'floor_loi', 'unit_loi', 'business_address', 'pic_penyewa', 'pic_phonenumber_penyewa', 'pic_email_penyewa', 'no_psm_or_addendum',
            ]) + ['psm_or_addendum_date' => substr((string) $agreement->psm_or_addendum_date, 0, 10) ?: null],
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
            'customDocs' => $saved->filter(fn ($d) => str_starts_with((string) $d->agreementdocument_id, 'CUSTOM_'))->values(),
            'propertyTypes' => self::PROPERTY_TYPES,
        ]);
    }

    public function storePsmOla(Request $request, string $eid)
    {
        $kind = $this->kind();
        $source = $this->sourceFor($request, $eid);
        $job = $source['job'];

        // The job's identity (Business ID, Tenant No, Unit, Property) is never taken from the form:
        // the fields are locked there, and a posted value can't override the source.
        $request->merge(Arr::only($source['values'], self::LOCKED_FIELDS));

        $validDocIds = $this->checklistDocuments()->pluck('agreementdocument_id')->all();

        $request->validate([
            'business_id' => 'required|integer',
            'business_name' => 'required|max:255',
            'tenant_no' => 'nullable|max:20',
            'trade_name' => 'nullable|max:255',
            'property_cd' => 'nullable|max:20',
            'floor_id' => 'nullable|max:20',
            'unit_id' => 'nullable|max:20',
            'floor_loi' => 'nullable|max:20',
            'unit_loi' => 'nullable|max:20',
            'business_address' => 'nullable|max:500',
            'pic_penyewa' => 'nullable|max:255',
            'pic_phonenumber_penyewa' => 'nullable|max:50',
            'pic_email_penyewa' => ['nullable', 'email', 'max:255', 'regex:/^[^@\s]+@[^@\s]+\.[A-Za-z]{2,}$/'],

            'no_psm_or_addendum' => 'required|max:150',
            'psm_or_addendum_date' => 'required|date',
            'pic_legal' => 'required|array|min:1',
            'pic_legal.*' => ['string', Rule::exists('pgsql2.sys_user_role', 'username')->where('role_id', self::ROLE_PIC_LEGAL)->where('status', 'A')],
            'pic_leasing' => 'required|array|min:1',
            'pic_leasing.*' => ['string', Rule::exists('pgsql2.sys_user_role', 'username')->where('role_id', self::ROLE_PIC_LEASING)->where('status', 'A')],

            'documents' => 'nullable|array',
            'documents.*.received' => 'nullable|boolean',
            'documents.*.note' => 'nullable|string|max:2000',
            'custom_documents' => 'nullable|array|max:50',
            'custom_documents.*.id' => 'nullable|string|max:50',
            'custom_documents.*.name' => 'required|string|max:255',
            'custom_documents.*.received' => 'nullable|boolean',
            'custom_documents.*.note' => 'nullable|string|max:2000',
        ], ['custom_documents.*.name.required' => 'Every added document needs a name.']);

        $submittedDocs = $kind['checklist'] ? (array) $request->input('documents', []) : [];

        if (array_diff(array_keys($submittedDocs), $validDocIds)) {
            return response()->json(['success' => false, 'message' => 'Unknown document in checklist.'], 422);
        }

        $username = $request->user()->username ?? 'system';

        // "Save as Draft" keeps the agreement out of Active (and unannounced); "Process" (the default) makes it Active.
        $isDraft = $request->input('action') === 'draft';
        $step = $isDraft ? self::STEP_DRAFT : self::STEP_ACTIVE;
        $dt = Carbon::now();
        $year = (int) $dt->year;
        $month = str_pad($dt->month, 2, '0', STR_PAD_LEFT);

        DB::connection('pgsql5')->beginTransaction();

        try {
            // Creates of one kind take turns, and the duplicate check runs inside that turn, so two
            // simultaneous saves can't both pass it. (Released automatically at commit / rollback.)
            DB::connection('pgsql5')->select('select pg_advisory_xact_lock(?)', [crc32('new-agreement-'.$kind['key'])]);

            if ($message = $this->duplicateMessage($request, null, (string) $source['cpny_id'])) {
                DB::connection('pgsql5')->rollBack();

                return response()->json(['success' => false, 'message' => $message], 422);
            }

            $next = $this->nextAutonbr($kind['doctype'], $year, $month, $username, 'New Agreement '.$kind['label']);

            // Same id shape as Item Request: DOCTYPE + yymm + 4-digit number.
            $agreementId = $kind['doctype'].substr((string) $year, 2).$month.sprintf('%04d', $next);

            $agreement = TrAgreement::create([
                'agreement_id' => $agreementId,
                'renewal_sequence' => 1,
                'agreement_date' => now(),
                'agreement_type' => $kind['from_psm'] ? $kind['type'] : self::psmOlaTypeFor($request->property_cd),

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
                'floor_loi' => $request->floor_loi,
                'unit_loi' => $request->unit_loi,
                'business_address' => $request->business_address,
                'pic_penyewa' => $request->pic_penyewa,
                'pic_phonenumber_penyewa' => $request->pic_phonenumber_penyewa,
                'pic_email_penyewa' => $request->pic_email_penyewa,
                'no_psm_or_addendum' => $request->no_psm_or_addendum,
                'psm_or_addendum_date' => $request->psm_or_addendum_date,
                'pic_legal' => TrAgreement::joinPicList((array) $request->pic_legal),
                'pic_leasing' => TrAgreement::joinPicList((array) $request->pic_leasing),

                // Same landing step as Agreement FU: a freshly saved agreement
                // is Active (the Active tab lists agreement_step_id = ACTIVE).
                'agreement_step_id' => $step,
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
                'agreement_step_id' => $step,
                'agreement_step_order' => 1,
                'response_date' => now(),
                'response_summary' => ($isDraft ? 'Agreement Saved as Draft (' : 'Agreement Created (').$kind['label'].')',
                'response_descr' => $agreement->business_name,
                'status_pekerjaan' => $step,
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

            if ($kind['checklist']) {
                $this->syncCustomDocuments($agreement, (array) $request->input('custom_documents', []), $username);
            }

            // Created straight to Process (not a Draft): Pengumpulan Dokumen + Process start now.
            if (! $isDraft) {
                $this->startProcessSheet($agreement, $username);
            }

            // A PSM / OLA job row is now being worked (On Progress): drop it off the pending list.
            // Addendums leave the IFCA job alone (the contract can have both).
            if ($kind['docs'] && $job) {
                $taken = StagingContractAgreement::query()
                    ->where('id', $job->id)
                    ->where('status', 'A')
                    ->update([
                        'status' => 'P',
                        'updated_by' => $username,
                        'updated_at' => now(),
                    ]);

                // Someone else took this job after the form was opened.
                if (! $taken) {
                    DB::connection('pgsql5')->rollBack();

                    return response()->json(['success' => false, 'message' => 'This job is no longer pending.'], 422);
                }
            }

            DB::connection('pgsql5')->commit();
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'Failed to create agreement.',
            ], 500);
        }

        if (! $isDraft) {
            $this->notifyPsmOla($agreement, 'created', $username);
        }

        return response()->json([
            'success' => true,
            'message' => $isDraft ? "Draft {$agreement->agreement_id} saved." : "Agreement {$agreement->agreement_id} created.",
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

        return $this->activeQuery([self::STEP_ACTIVE, self::STEP_COMPLETED, self::STEP_CANCELLED, self::STEP_DRAFT])->findOrFail($id);
    }

    // Active agreement the current user may change: its creator or a PIC Legal.
    // $allowDraft: only the edit form / update accept a Draft; every other action needs an Active one.
    protected function updatableAgreementOrFail(string $eid, bool $allowDraft = false): TrAgreement
    {
        if ($allowDraft) {
            $id = Hashids::decode($eid)[0] ?? null;

            abort_if(! $id, 404);

            $agreement = $this->activeQuery([self::STEP_ACTIVE, self::STEP_DRAFT])->findOrFail($id);
        } else {
            $agreement = $this->activeAgreementOrFail($eid);
        }

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
        $draft = $agreement->agreement_step_id === self::STEP_DRAFT;

        $company = MsCompany::query()->where('cpny_id', $agreement->cpny_id)->first(['cpny_id', 'cpny_name']);

        $kind = $this->kind();

        return view('pages.legal-new-agreement.psm-ola-agreement-form', [
            'draft' => $draft,
            'kind' => $kind,
            'psmOlaNo' => $this->psmOlaNoFor($agreement),
            'psmOlaDate' => $this->psmOlaDateFor($agreement),
            'related' => $this->relatedFor($agreement),
            'eid' => $eid,
            'agreement' => $agreement,
            'companyName' => $company?->cpny_name ?? $agreement->cpny_id,
            'documents' => $kind['checklist']
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
            'processes' => $this->processesFor($agreement, (bool) $draft),
            'picLegalNames' => array_values($this->picLabels($agreement->picLegalList())),
            'picLeasingNames' => array_values($this->picLabels($agreement->picLeasingList())),
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
                        $fmt($a->working_start_date) ? 'Start: '.$fmt($a->working_start_date) : null,
                        $fmt($a->working_end_date) ? 'End: '.$fmt($a->working_end_date) : null,
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
     * The process sheet, one row per process, all in tr_agreement_activity
     * ('PROCESS:<ms_agreement_category id>'). Every process has a Start and an
     * End date (working_start_date / working_end_date) the creator picks, plus an
     * optional note in response_descr; response_summary is the process name.
     *  - Two system rows are added when the agreement is Processed (Draft -> Process,
     *    or Create -> Process; see startProcessSheet()):
     *      Pengumpulan Dokumen starts on the earliest date a checklist document was
     *      received and, once every required document is in, ends on the latest one
     *      (both derived live; a saved End overrides the derived one), and
     *      Process PSM / OLA starts on the day Process was clicked.
     *    Neither Start is editable; the creator sets their End (Process) or may override it. Agreements Processed
     *    before this existed show both too (Process starting on its activation date).
     *  - Everything else is added by the creator with "Add Process" from the
     *    PROCESS rows of ms_agreement_category, in the order they were added.
     */
    protected function processesFor(TrAgreement $agreement, bool $draft = false)
    {
        $categories = $this->processCategories();

        $activities = TrAgreementActivity::query()
            ->where('agreement_id', $agreement->agreement_id)
            ->whereNull('deleted_at')
            ->where('agreement_activity_type', 'like', self::PROCESS_PREFIX.'%')
            ->orderBy('id')
            ->get();

        $saved = $activities
            ->keyBy(fn ($a) => substr($a->agreement_activity_type, strlen(self::PROCESS_PREFIX)));

        // Legacy 'PROCESS:PRINT' rows saved before Start / End existed only carry response_date.
        // $key is the row's key: the category id, or "<id>#<n>" for the n-th time it was added.
        $toRow = function (string $key, ?TrAgreementActivity $row) use ($categories, $agreement) {
            $id = $this->processBaseId($key);
            $nth = str_contains($key, '#') ? (int) substr($key, strpos($key, '#') + 1) : 1;
            $type = $categories[$id]->agreementcategory_type ?? 'PROCESS';
            $isDoc = $id === self::PROCESS_DOC;
            $isStart = $id === self::PROCESS_START;
            $legacy = ! $isDoc && ! $isStart && $row && ! $row->working_start_date && ! $row->working_end_date;

            return (object) [
                'type' => $type,
                'process_id' => $key,
                'descr' => $isStart ? $this->processStartLabel() : (($categories[$id]->agreementcategory_descr ?? $row?->response_summary ?? $id).($nth > 1 ? " #{$nth}" : '')),
                'editable' => true,
                'removable' => ! $isDoc && ! $isStart,
                'start_locked' => $isDoc || $isStart,
                'date_start' => match (true) {
                    $isDoc => $this->earliestDocumentReceived($agreement->agreement_id),
                    $isStart => $row?->working_start_date ?? ($agreement->agreement_step_created_at ? Carbon::parse($agreement->agreement_step_created_at) : null),
                    default => $legacy ? $row->response_date : $row?->working_start_date,
                },
                'date_end' => $legacy ? $row->response_date : ($row?->working_end_date ?? ($isDoc ? $this->documentsCompletedAt($agreement->agreement_id) : null)),
                'note' => $row?->response_descr,
            ];
        };

        $rows = collect();

        if ($this->kind()['checklist']) {
            $rows->push($toRow(self::PROCESS_DOC, $saved->get(self::PROCESS_DOC)));
        }

        // A Draft is only collecting documents: the Process step (and everything after it) starts once Processed.
        if (! $draft) {
            $rows->push($toRow(self::PROCESS_START, $saved->get(self::PROCESS_START)));
        }

        foreach ($saved as $id => $row) {
            if (! $draft && ! in_array($id, [self::PROCESS_DOC, self::PROCESS_START], true)) {
                $rows->push($toRow($id, $row));
            }
        }

        $used = $rows->pluck('process_id')->map(fn ($k) => $this->processBaseId($k))->all();
        $routing = $rows->where('type', 'ROUTING')->values();

        return (object) [
            'rows' => $rows->where('type', '!=', 'ROUTING')->values(),
            'routing' => $routing,
            // Routing starts once Cetak is on the list (or already has rows, e.g. saved before this rule).
            // Others: only once it has routing rows; the Route button reveals the section otherwise.
            'routingVisible' => $this->kind()['routing'] && ($routing->isNotEmpty() || (! ($this->kind()['routing_optional'] ?? false) && in_array('PRINT', $used, true))),
            // For the Add Process dropdown: [type => [id => name]], PROCESS before ROUTING.
            // A process can be added more than once (Cetak, Konfirmasi, Review ...), so used ones stay.
            'available' => $categories
                ->reject(fn ($c) => $c->agreementcategory_system)
                ->groupBy('agreementcategory_type')
                ->sortKeys()
                ->map(fn ($group) => $group->pluck('agreementcategory_descr', 'agreementcategory_id')->all())
                ->all(),
        ];
    }

    protected function processStartLabel(): string
    {
        return 'Process '.$this->kind()['label'];
    }

    /**
     * Called when an agreement is Processed (Draft -> Process, or created straight to Process):
     * puts Pengumpulan Dokumen (PSM / OLA only) and Process on its process sheet. Process
     * starts today; Pengumpulan Dokumen's Start is derived from the documents. Safe to repeat.
     */
    protected function startProcessSheet(TrAgreement $agreement, string $username): void
    {
        $ids = [self::PROCESS_START => $this->processStartLabel()];

        if ($this->kind()['checklist']) {
            $ids = [self::PROCESS_DOC => $this->processCategories()[self::PROCESS_DOC]->agreementcategory_descr ?? 'Pengumpulan Dokumen'] + $ids;
        }

        foreach ($ids as $id => $descr) {
            $exists = TrAgreementActivity::query()
                ->where('agreement_id', $agreement->agreement_id)
                ->where('agreement_activity_type', $this->processType($id))
                ->whereNull('deleted_at')
                ->exists();

            if (! $exists) {
                TrAgreementActivity::create([
                    'agreement_id' => $agreement->agreement_id,
                    'cpny_id' => $agreement->cpny_id,
                    'agreement_activity_type' => $this->processType($id),
                    'agreement_step_id' => $agreement->agreement_step_id,
                    'agreement_step_order' => $agreement->agreement_step_order,
                    'working_start_date' => $id === self::PROCESS_START ? now()->startOfDay() : null,
                    'response_date' => now(),
                    'response_summary' => $descr,
                    'status_pekerjaan' => $agreement->agreement_step_id,
                    'status' => 'A',
                    'created_by' => $username,
                ]);
            }
        }
    }

    // Active PROCESS names from ms_agreement_category, keyed by id, in master order.
    protected function processCategories()
    {
        return MsAgreementCategory::query()->activeProcesses()->get()
            ->reject(fn ($c) => ! $this->kind()['routing'] && $c->agreementcategory_type === 'ROUTING')
            ->keyBy('agreementcategory_id');
    }

    // When the checklist became complete: the latest received date once every required document
    // (every document, if none is required) has been received; null while any is still missing.
    protected function documentsCompletedAt(string $agreementId)
    {
        $docs = TrAgreementDocument::query()
            ->where('agreement_id', $agreementId)
            ->whereNull('deleted_at')
            ->get(['agreementdocument_required', 'agreementdocument_received', 'agreementdocument_received_at']);

        $needed = $docs->where('agreementdocument_required', true);
        $needed = $needed->isEmpty() ? $docs : $needed;

        if ($needed->isEmpty() || $needed->contains(fn ($d) => ! $d->agreementdocument_received)) {
            return null;
        }

        $latest = $needed->max(fn ($d) => $d->agreementdocument_received_at);

        return $latest ? Carbon::parse($latest) : null;
    }

    // Earliest date any checklist document was received; null while none has been.
    protected function earliestDocumentReceived(string $agreementId)
    {
        $at = TrAgreementDocument::query()
            ->where('agreement_id', $agreementId)
            ->whereNull('deleted_at')
            ->where('agreementdocument_received', true)
            ->whereNotNull('agreementdocument_received_at')
            ->min('agreementdocument_received_at');

        return $at ? Carbon::parse($at) : null;
    }

    // Activity type a process is stored under.
    protected function processType(string $id): string
    {
        return self::PROCESS_PREFIX.$id;
    }

    // "PRINT#2" (second Cetak) -> "PRINT".
    protected function processBaseId(string $key): string
    {
        return explode('#', $key)[0];
    }

    // Key for a process being added again: the id itself while no active row holds it, else "<id>#<n>".
    protected function nextProcessKey(TrAgreement $agreement, string $id): string
    {
        $types = TrAgreementActivity::query()
            ->where('agreement_id', $agreement->agreement_id)
            ->where(fn ($q) => $q->where('agreement_activity_type', $this->processType($id))
                ->orWhere('agreement_activity_type', 'like', $this->processType($id).'#%'))
            ->get(['agreement_activity_type', 'deleted_at']);

        $baseActive = $types->contains(fn ($t) => $t->agreement_activity_type === $this->processType($id) && ! $t->deleted_at);

        if (! $baseActive) {
            return $id;
        }

        $max = $types->map(fn ($t) => str_contains($t->agreement_activity_type, '#') ? (int) substr($t->agreement_activity_type, strpos($t->agreement_activity_type, '#') + 1) : 1)->max();

        return $id.'#'.($max + 1);
    }

    /**
     * Creator / PIC Legal: save one or more processes (Start / End + notes). A process
     * with no row yet is added (this is what "Add Process" posts); process[<id>][remove]
     * takes an added one off the sheet again.
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
            'process.*.remove' => 'nullable|boolean',
            'process.*.add' => 'nullable|boolean',
            'process.*.date_start' => 'nullable|date',
            'process.*.date_end' => 'nullable|date',
            'process.*.note' => 'nullable|string|max:2000',
        ]);

        $submitted = (array) $request->input('process', []);
        $categories = $this->processCategories();

        // A process dropped from the master can still be edited on agreements that already hold it.
        $existing = TrAgreementActivity::query()
            ->where('agreement_id', $agreement->agreement_id)
            ->where('agreement_activity_type', 'like', self::PROCESS_PREFIX.'%')
            ->whereNull('deleted_at')
            ->pluck('agreement_activity_type')
            ->map(fn ($t) => substr($t, strlen(self::PROCESS_PREFIX)))
            ->all();

        $known = array_merge($categories->keys()->all(), array_map(fn ($k) => $this->processBaseId($k), $existing));

        if (array_diff(array_map(fn ($k) => $this->processBaseId((string) $k), array_keys($submitted)), $known)) {
            return response()->json(['success' => false, 'message' => 'Unknown process.'], 422);
        }

        $changed = [];

        DB::connection('pgsql5')->beginTransaction();

        try {
            foreach ($submitted as $key => $input) {
                $key = (string) $key;
                $id = $this->processBaseId($key);

                // "Add Process" on one already on the sheet adds another copy of it (Cetak #2 ...).
                if (! empty($input['add']) && ! in_array($id, [self::PROCESS_DOC, self::PROCESS_START], true)) {
                    $key = $this->nextProcessKey($agreement, $id);
                }

                $isDoc = $id === self::PROCESS_DOC;
                $isStart = $id === self::PROCESS_START;
                $system = $isDoc || $isStart;
                $nth = str_contains($key, '#') ? (int) substr($key, strpos($key, '#') + 1) : 1;
                $descr = $isStart ? $this->processStartLabel() : (($categories[$id]->agreementcategory_descr ?? $id).($nth > 1 ? " #{$nth}" : ''));
                $note = filled($input['note'] ?? null) ? $input['note'] : null;
                $start = ! $system && ! empty($input['date_start']) ? Carbon::parse($input['date_start'])->startOfDay() : null;
                $end = ! empty($input['date_end']) ? Carbon::parse($input['date_end'])->startOfDay() : null;

                $row = TrAgreementActivity::query()
                    ->where('agreement_id', $agreement->agreement_id)
                    ->where('agreement_activity_type', $this->processType($key))
                    ->orderByRaw('deleted_at is not null')
                    ->orderBy('id')
                    ->first();

                // A process that was removed earlier keeps its (retired) row: bring it back
                // instead of inserting a second one, so a process is always one row.
                $retired = $row && $row->deleted_at ? $row : null;
                $row = $retired ? null : $row;

                // Process's Start is fixed to the day Process was clicked (its own row, else the activation date).
                if ($isStart) {
                    $start = Carbon::parse($row?->working_start_date ?? $retired?->working_start_date ?? $agreement->agreement_step_created_at ?? now())->startOfDay();
                }

                if (! empty($input['remove'])) {
                    if ($system) {
                        DB::connection('pgsql5')->rollBack();

                        return response()->json(['success' => false, 'message' => "{$descr} can't be removed."], 422);
                    }

                    if ($row) {
                        $row->update(['deleted_by' => $username, 'deleted_at' => now()]);
                        $changed[] = $descr;
                    }

                    continue;
                }

                // Routing only starts once Cetak is on the list.
                if (! $row && ! ($this->kind()['routing_optional'] ?? false) && ($categories[$id]->agreementcategory_type ?? null) === 'ROUTING' && ! in_array('PRINT', array_map(fn ($k) => $this->processBaseId($k), $existing), true)) {
                    DB::connection('pgsql5')->rollBack();

                    return response()->json(['success' => false, 'message' => "{$descr}: add Cetak before routing."], 422);
                }

                if (! $isDoc && ! $start) {
                    DB::connection('pgsql5')->rollBack();

                    return response()->json(['success' => false, 'message' => "{$descr}: pick a Start date."], 422);
                }

                $effectiveStart = $isDoc ? $this->earliestDocumentReceived($agreement->agreement_id)?->startOfDay() : $start;

                if ($end && $effectiveStart && $end->lt($effectiveStart)) {
                    DB::connection('pgsql5')->rollBack();

                    return response()->json(['success' => false, 'message' => "{$descr}: the End date can't be before the Start date."], 422);
                }

                // Pengumpulan Dokumen's End left at the derived date stays derived (not frozen into the row).
                if ($isDoc && $end && ! $row?->working_end_date && $end->equalTo(optional($this->documentsCompletedAt($agreement->agreement_id))->startOfDay())) {
                    $end = null;
                }

                // A system row with nothing entered and nothing saved: don't create an empty row.
                if ($system && ! $row && ! $end && ! $note) {
                    continue;
                }

                $fmt = fn ($d) => optional($d)->format('Y-m-d');

                if ($row
                    && $fmt($row->working_start_date) === $fmt($start)
                    && $fmt($row->working_end_date) === $fmt($end)
                    && (string) $row->response_descr === (string) $note) {
                    continue;
                }

                // response_date = when the creator updated this process.
                $attrs = [
                    'working_start_date' => $start,
                    'working_end_date' => $end,
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
                        'agreement_activity_type' => $this->processType($key),
                        'agreement_step_id' => $agreement->agreement_step_id,
                        'agreement_step_order' => $agreement->agreement_step_order,
                        'response_summary' => $descr,
                        'status_pekerjaan' => $agreement->agreement_step_id,
                        'status' => 'A',
                        'created_by' => $username,
                    ]);
                }

                $changed[] = $descr;
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
    /**
     * "Process" on a Draft (view modal Actions): Draft -> Active, as if it had been created Active.
     * Only its creator or a PIC Legal; the number / tenant + unit duplicate checks run now.
     */
    public function activatePsmOla(Request $request, string $eid)
    {
        $id = Hashids::decode($eid)[0] ?? null;

        abort_if(! $id, 404);

        $agreement = $this->activeQuery(self::STEP_DRAFT)->findOrFail($id);
        $username = $request->user()->username ?? 'system';

        if (! $agreement->canBeUpdatedBy($username)) {
            return response()->json(['success' => false, 'message' => 'Only the creator or PIC Legal can process this draft.'], 403);
        }

        $request->merge($agreement->only(['no_psm_or_addendum', 'tenant_no', 'unit_id']));

        if ($message = $this->duplicateMessage($request, $agreement, (string) $agreement->cpny_id)) {
            return response()->json(['success' => false, 'message' => $message], 422);
        }

        DB::connection('pgsql5')->beginTransaction();

        try {
            $agreement->update([
                'agreement_step_id' => self::STEP_ACTIVE,
                'agreement_step_order' => $agreement->agreement_step_order + 1,
                'agreement_step_created_user' => $username,
                'agreement_step_created_at' => now(),
                'updated_user' => $username,
            ]);

            $this->logActivity($agreement, self::ACTIVITY_UPDATE_PEMBUATAN, self::STEP_ACTIVE, 'Agreement Processed ('.$this->kind()['label'].')', $agreement->business_name, $username);
            $this->startProcessSheet($agreement, $username);

            DB::connection('pgsql5')->commit();
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'Failed to process the draft.',
            ], 500);
        }

        $this->notifyPsmOla($agreement, 'created', $username);

        return response()->json([
            'success' => true,
            'message' => "Agreement {$agreement->agreement_id} processed and now Active.",
            'counts' => $this->tabCounts(),
        ]);
    }

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

            // The IFCA job is finished too (P On Progress -> C Completed). Addendums have none.
            if ($this->kind()['docs']) {
                $this->moveSourceJob($agreement, 'P', 'C', $username);
            }

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

        // Addendums made from this agreement must be cancelled (or finished) first.
        if ($addendums = TrAgreement::activeAddendumIds($agreement->agreement_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Cancel its Active addendum(s) first: '.implode(', ', $addendums).'.',
            ], 422);
        }

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

            // The job this PSM / OLA was made from is pending again (it is On Progress, or still
            // 'C' on a row from before the P / C split).
            if ($kind['docs']) {
                $this->moveSourceJob($agreement, ['P', 'C'], 'A', $username);
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

    /**
     * Keeps the IFCA job's status in step with its PSM / OLA:
     *   A pending (no agreement) -> P on progress (agreement Active) -> C completed.
     * (The match itself lives on StagingContractAgreement so Agreement FU can release the job too.)
     */
    protected function moveSourceJob(TrAgreement $agreement, string|array $from, string $to, string $username): void
    {
        StagingContractAgreement::moveFor($agreement, $from, $to, $username);
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

            // The job follows the agreement back to On Progress: a cancelled one gave it back
            // (Pending), a completed one had finished it.
            if ($this->kind()['docs']) {
                $this->moveSourceJob($agreement, $wasCancelled ? 'A' : 'C', 'P', $username);
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
        $agreement = $this->updatableAgreementOrFail($eid, true);
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
        $no = $this->kind()['no_label'];
        $dateLabel = $this->kind()['date_label'];

        $labels = [
            'business_id' => 'Business ID', 'business_name' => 'Tenant Company Name', 'tenant_no' => 'Tenant No',
            'trade_name' => 'Tenant Name', 'floor_id' => 'Floor', 'unit_id' => 'Unit',
            'floor_loi' => 'Floor LOI', 'unit_loi' => 'Unit LOI', 'business_address' => 'Address',
            'pic_penyewa' => 'PIC Name', 'pic_phonenumber_penyewa' => 'PIC Phone', 'pic_email_penyewa' => 'Email',
            'no_psm_or_addendum' => $no,
            'psm_or_addendum_date' => $dateLabel,
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
        // Active, or a Draft (its documents can be filled in before it is Processed).
        $id = Hashids::decode($eid)[0] ?? null;

        abort_if(! $id, 404);

        $agreement = $this->activeQuery([self::STEP_ACTIVE, self::STEP_DRAFT])->findOrFail($id);
        $username = $request->user()->username ?? 'system';

        if (! $agreement->canBeUpdatedBy($username)) {
            return response()->json(['success' => false, 'message' => 'Only the creator or PIC Legal can update the documents.'], 403);
        }

        $request->validate([
            'documents' => 'required|array',
            'documents.*.received' => 'nullable|boolean',
            'documents.*.note' => 'nullable|string|max:2000',
            // Documents added in Update mode: free-text name, for this agreement only (never the master).
            'new_documents' => 'nullable|array|max:50',
            'new_documents.*.name' => 'required|string|max:255',
            'new_documents.*.received' => 'nullable|boolean',
            'new_documents.*.note' => 'nullable|string|max:2000',
        ], ['new_documents.*.name.required' => 'Every added document needs a name.']);

        $submitted = (array) $request->input('documents', []);
        $added = [];
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

            foreach ((array) $request->input('new_documents', []) as $row) {
                $received = (bool) ($row['received'] ?? false);
                $name = trim((string) $row['name']);
                $added[] = $name;

                TrAgreementDocument::create([
                    'agreement_id' => $agreement->agreement_id,
                    'cpny_id' => $agreement->cpny_id,
                    'agreementdocument_id' => 'CUSTOM_'.strtoupper(Str::random(10)),
                    'agreementdocument_descr' => $name,
                    'agreementdocument_required' => false,
                    'agreementdocument_received' => $received,
                    'agreementdocument_received_at' => $received ? now() : null,
                    'agreementdocument_note' => $row['note'] ?? null,
                    'status' => 'A',
                    'created_by' => $username,
                ]);
            }

            TrAgreementActivity::create([
                'agreement_id' => $agreement->agreement_id,
                'cpny_id' => $agreement->cpny_id,
                'agreement_activity_type' => self::ACTIVITY_DOCUMENTS,
                'agreement_step_id' => $agreement->agreement_step_id,
                'agreement_step_order' => $agreement->agreement_step_order,
                'response_date' => now(),
                'response_summary' => 'Documents Updated',
                'response_descr' => collect([
                    $newlyReceived ? "Received:\n".implode("\n", $newlyReceived) : null,
                    $added ? "Added:\n".implode("\n", $added) : null,
                ])->filter()->implode("\n\n") ?: null,
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
        $agreement = $this->updatableAgreementOrFail($eid, true);

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
        $agreement = $this->updatableAgreementOrFail($eid, true);

        // Company and Property Type are intentionally not accepted here.
        // Business ID, Tenant No, Unit and Company/Property are fixed; they are not read from the request.
        $request->validate([
            'business_name' => 'required|max:255',
            'trade_name' => 'nullable|max:255',
            'floor_loi' => 'nullable|max:20',
            'unit_loi' => 'nullable|max:20',
            'business_address' => 'nullable|max:500',
            'pic_penyewa' => 'nullable|max:255',
            'pic_phonenumber_penyewa' => 'nullable|max:50',
            'pic_email_penyewa' => ['nullable', 'email', 'max:255', 'regex:/^[^@\s]+@[^@\s]+\.[A-Za-z]{2,}$/'],

            'no_psm_or_addendum' => 'required|max:150',
            'psm_or_addendum_date' => 'required|date',
            'pic_legal' => 'required|array|min:1',
            'pic_legal.*' => ['string', Rule::exists('pgsql2.sys_user_role', 'username')->where('role_id', self::ROLE_PIC_LEGAL)->where('status', 'A')],
            'pic_leasing' => 'required|array|min:1',
            'pic_leasing.*' => ['string', Rule::exists('pgsql2.sys_user_role', 'username')->where('role_id', self::ROLE_PIC_LEASING)->where('status', 'A')],

            'documents' => 'nullable|array',
            'documents.*.received' => 'nullable|boolean',
            'documents.*.note' => 'nullable|string|max:2000',
            'custom_documents' => 'nullable|array|max:50',
            'custom_documents.*.id' => 'nullable|string|max:50',
            'custom_documents.*.name' => 'required|string|max:255',
            'custom_documents.*.received' => 'nullable|boolean',
            'custom_documents.*.note' => 'nullable|string|max:2000',
        ], ['custom_documents.*.name.required' => 'Every added document needs a name.']);

        $username = $request->user()->username ?? 'system';
        $kind = $this->kind();
        $submittedDocs = $kind['checklist'] ? (array) $request->input('documents', []) : [];

        // The saved Tenant No / Unit stand, whatever was posted.
        $request->merge($agreement->only(self::LOCKED_FIELDS));

        if ($message = $this->duplicateMessage($request, $agreement, (string) $agreement->cpny_id)) {
            return response()->json(['success' => false, 'message' => $message], 422);
        }

        $before = $this->trackedValues($agreement);

        // A Draft stays a Draft on "Save as Draft" and becomes Active on "Process"; an Active agreement just saves.
        $wasDraft = $agreement->agreement_step_id === self::STEP_DRAFT;
        $processing = $wasDraft && $request->input('action') === 'process';

        DB::connection('pgsql5')->beginTransaction();

        try {
            // business_id / tenant_no / floor_id / unit_id are fixed (IFCA) and deliberately not updated;
            // Legal's own floor / unit go to floor_loi / unit_loi.
            $agreement->update([
                'business_name' => $request->business_name,
                'trade_name' => $request->trade_name,
                'floor_loi' => $request->floor_loi,
                'unit_loi' => $request->unit_loi,
                'business_address' => $request->business_address,
                'pic_penyewa' => $request->pic_penyewa,
                'pic_phonenumber_penyewa' => $request->pic_phonenumber_penyewa,
                'pic_email_penyewa' => $request->pic_email_penyewa,
                'no_psm_or_addendum' => $request->no_psm_or_addendum,
                'psm_or_addendum_date' => $request->psm_or_addendum_date,
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

            if ($kind['checklist']) {
                $this->syncCustomDocuments($agreement, (array) $request->input('custom_documents', []), $username);
            }

            if ($processing) {
                $agreement->update([
                    'agreement_step_id' => self::STEP_ACTIVE,
                    'agreement_step_order' => $agreement->agreement_step_order + 1,
                    'agreement_step_created_user' => $username,
                    'agreement_step_created_at' => now(),
                ]);

                $this->startProcessSheet($agreement, $username);
            }

            TrAgreementActivity::create([
                'agreement_id' => $agreement->agreement_id,
                'cpny_id' => $agreement->cpny_id,
                'agreement_activity_type' => self::ACTIVITY_UPDATE_PEMBUATAN,
                'agreement_step_id' => $agreement->agreement_step_id,
                'agreement_step_order' => $agreement->agreement_step_order,
                'response_date' => now(),
                'response_summary' => $processing
                    ? 'Agreement Processed ('.$kind['label'].')'
                    : ($wasDraft ? 'Draft Updated ('.$kind['label'].')' : 'Agreement Updated ('.$kind['label'].')'),
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

        // A draft isn't announced to anyone until it is Processed (then it reads as a new agreement).
        if ($processing) {
            $this->notifyPsmOla($agreement, 'created', $username);
        } elseif (! $wasDraft) {
            $this->notifyPsmOla($agreement, 'updated', $username);
        }

        return response()->json([
            'success' => true,
            'message' => $processing
                ? "Agreement {$agreement->agreement_id} processed and now Active."
                : ($wasDraft ? "Draft {$agreement->agreement_id} saved." : "Agreement {$agreement->agreement_id} updated."),
            'counts' => $this->tabCounts(),
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

    // /legal-new-agreement/{addendum|others}/create-agreement/{eid}[?src=psm]: that page with the create modal opened.
    public function addendumCreateLink(string $eid)
    {
        return $this->psmOla($eid);
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
    // Others is the same page as Addendum (see kind()), minus the Routing section.
    public function others()
    {
        return $this->psmOla();
    }
}
