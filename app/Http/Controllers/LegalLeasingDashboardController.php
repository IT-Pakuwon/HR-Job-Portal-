<?php

namespace App\Http\Controllers;

use App\Models\MsCompany;
use App\Models\TrAgreement;
use App\Models\TrAgreementDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Vinkla\Hashids\Facades\Hashids;

/**
 * Dashboard Legal Leasing: the two document lists next to Waiting Approval / Approval History.
 *
 *  - Incomplete Document: New Agreement documents (PSM, OLA, Addendum, Others) that still have a required document
 *    the tenant has not handed in (tr_agreement_document.agreementdocument_received = false).
 *  - Follow Up Document: Agreement FU documents that are still being followed up (Active / Hold / Escalated).
 *
 * Who sees what is the same rule as the Agreement pages: Legal managers and full-data-scope users see everything,
 * everybody else the agreements they created or are PIC Legal / PIC Leasing of.
 */
class LegalLeasingDashboardController extends Controller
{
    /** Types made by New Agreement; 'PEMBUATAN' is the legacy value of a PSM/OLA created before the type was split. */
    private const INCOMPLETE_TYPES = ['PSM', 'OLA', 'PEMBUATAN', 'ADDENDUM', 'OTHERS'];

    /** An agreement that is done (Completed) can still be missing documents; Draft / Cancelled never count. */
    private const INCOMPLETE_STEPS = ['ACTIVE', 'COMPLETED'];

    /** A follow-up is "not completed yet" while it is in one of these steps. */
    private const FU_OPEN_STEPS = ['ACTIVE', 'HOLD', 'ESCALATED'];

    private const TYPE_LABELS = [
        'PSM' => 'PSM', 'OLA' => 'OLA', 'PEMBUATAN' => 'PSM / OLA', 'ADDENDUM' => 'Addendum', 'OTHERS' => 'Others',
        'FU_PSM' => 'FU PSM', 'FU_OLA' => 'FU OLA', 'FU_ADDENDUM' => 'FU Addendum', 'FU_OTHERS' => 'FU Others',
    ];

    private const STEP_LABELS = ['ACTIVE' => 'Active', 'COMPLETED' => 'Completed', 'HOLD' => 'Hold', 'ESCALATED' => 'Escalated'];

    public function incompleteJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $query = $this->visible(TrAgreement::query()
            ->whereNull('deleted_at')
            ->whereIn('agreement_type', self::INCOMPLETE_TYPES)
            ->whereIn('agreement_step_id', self::INCOMPLETE_STEPS)
            ->whereExists(function ($sub) {
                $sub->selectRaw('1')
                    ->from((new TrAgreementDocument())->getTable().' as d')
                    ->whereColumn('d.agreement_id', 'tr_agreement.agreement_id')
                    ->whereNull('d.deleted_at')
                    ->where('d.agreementdocument_required', true)
                    ->whereRaw('coalesce(d.agreementdocument_received, false) = false');
            }));

        $type = strtoupper((string) $request->query('type', ''));

        if (in_array($type, ['PSM', 'OLA'], true)) {
            $query->whereIn('agreement_type', [$type, 'PEMBUATAN']);
        } elseif (in_array($type, ['ADDENDUM', 'OTHERS'], true)) {
            $query->where('agreement_type', $type);
        }

        return $this->page($request, $query, 'created_at', true);
    }

    public function followUpJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $query = $this->visible(TrAgreement::followUp()
            ->whereNull('deleted_at')
            ->whereIn('agreement_step_id', self::FU_OPEN_STEPS));

        $type = strtoupper((string) $request->query('type', ''));

        if (in_array($type, ['PSM', 'OLA', 'ADDENDUM', 'OTHERS'], true)) {
            $query->where('agreement_type', 'FU_'.$type);
        }

        return $this->page($request, $query, 'agreement_step_created_at', false);
    }

    /** The Agreement pages' visibility rule, so the dashboard never shows more than those pages do. */
    private function visible($query)
    {
        $user = auth()->user();

        if (! app(LegalAgreementController::class)->isManagerRole() && ! $user->hasFullDataScope()) {
            $query->where(function ($q) use ($user) {
                $q->where('created_user', $user->username)
                    ->orWhere(fn ($q2) => $q2->wherePicLegalOrLeasing($user->username));
            });
        }

        return $query;
    }

    private function page(Request $request, $query, string $sinceColumn, bool $withDocuments)
    {
        if (is_string($request->query('cpny_id')) && $request->filled('cpny_id')) {
            $query->where('cpny_id', $request->query('cpny_id'));
        }

        $search = $request->query('search');

        if (is_string($search) && trim($search) !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('agreement_id', 'ilike', "%{$search}%")
                    ->orWhere('no_psm_or_addendum', 'ilike', "%{$search}%")
                    ->orWhere('business_name', 'ilike', "%{$search}%")
                    ->orWhere('trade_name', 'ilike', "%{$search}%")
                    ->orWhere('unit_loi', 'ilike', "%{$search}%")
                    ->orWhere('created_user', 'ilike', "%{$search}%");
            });
        }

        $perPage = in_array((int) $request->query('per_page'), [10, 25, 50, 100], true) ? (int) $request->query('per_page') : 10;
        $page = max(1, (int) $request->query('page', 1));
        $total = (clone $query)->count();

        // the longest-waiting first: that is what needs chasing
        $rows = $query->orderByRaw("coalesce({$sinceColumn}, created_at) asc")->orderBy('id')
            ->forPage($page, $perPage)->get();

        $docs = TrAgreementDocument::query()
            ->whereIn('agreement_id', $rows->pluck('agreement_id'))
            ->whereNull('deleted_at')
            ->get(['agreement_id', 'agreementdocument_descr', 'agreementdocument_required', 'agreementdocument_received'])
            ->groupBy('agreement_id');

        $companies = MsCompany::query()->pluck('cpny_name', 'cpny_id');
        $now = Carbon::now();

        $data = $rows->map(function (TrAgreement $a) use ($docs, $companies, $now, $sinceColumn, $withDocuments) {
            $mine = $docs->get($a->agreement_id, collect());
            $missing = $mine->filter(fn ($d) => $d->agreementdocument_required && ! $d->agreementdocument_received)
                ->pluck('agreementdocument_descr')->values()->all();
            $since = $a->{$sinceColumn} ?? $a->created_at;

            return [
                'eid' => Hashids::encode($a->id),
                'agreement_id' => $a->agreement_id,
                'type' => $a->agreement_type,
                'type_label' => self::TYPE_LABELS[$a->agreement_type] ?? ($a->agreement_type ?: 'Follow Up'),
                'no_psm' => $a->no_psm_or_addendum,
                'company' => $companies->get($a->cpny_id, $a->cpny_id),
                'tenant' => $a->trade_name ?: $a->business_name,
                'unit' => $a->unit_loi ?: $a->unit_id,
                'step' => self::STEP_LABELS[$a->agreement_step_id] ?? $a->agreement_step_id,
                'step_id' => $a->agreement_step_id,
                'since' => $since ? Carbon::parse($since)->format('Y-m-d') : null,
                'days' => $since ? (int) Carbon::parse($since)->diffInDays($now) : null,
                'pic_legal' => TrAgreement::picDisplayNames($a->picLegalList()),
                'docs_total' => $mine->count(),
                'docs_received' => $mine->where('agreementdocument_received', true)->count(),
                'missing' => $withDocuments ? $missing : [],
                'url' => $this->urlFor($a),
            ];
        })->values();

        return response()->json([
            'data' => $data,
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => max(1, (int) ceil($total / $perPage)),
            'from' => $total ? (($page - 1) * $perPage) + 1 : 0,
            'to' => $total ? min($page * $perPage, $total) : 0,
        ]);
    }

    /** The agreement's own page: New Agreement types open their view page, follow-ups the Agreement FU page. */
    private function urlFor(TrAgreement $a): string
    {
        $eid = Hashids::encode($a->id);

        return match ($a->agreement_type) {
            'PSM', 'OLA', 'PEMBUATAN' => route('legal-new-agreement.psm-ola.view', $eid, false),
            'ADDENDUM' => route('legal-new-agreement.addendum.view', $eid, false),
            'OTHERS' => route('legal-new-agreement.others.view', $eid, false),
            default => '/show-legal-agreement/'.$eid,
        };
    }
}
