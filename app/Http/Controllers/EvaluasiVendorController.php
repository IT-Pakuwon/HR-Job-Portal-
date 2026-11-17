<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\HasAutonbr;
use App\Models\MsTemplateVendorPerfomance;
use App\Models\MsTemplateVendorPerfomanceDetail;
use App\Models\MsTemplateVendorPerfomanceScore;
use App\Models\TrKontrak;
use App\Models\TrPO;
use App\Models\TrApproval;
use App\Models\TrVendorPerfomance;
use App\Models\TrVendorPerfomanceDetail;
use App\Models\User;
use App\Services\VendorEvaluationScoring;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Vinkla\Hashids\Facades\Hashids;

class EvaluasiVendorController extends Controller
{
    use HasAutonbr;

    private function showUrlEvaluasi(int $id): string
    {
        return route('showevaluasivendor', Hashids::encode($id));
    }

    public const STATUSES = ['P' => 'On Progress', 'R' => 'Rejected', 'D' => 'Revised', 'C' => 'Completed'];

    private function sources()
    {
        $po = TrPO::query()->selectRaw("id as source_id, 'SPK' as source_type, ponbr as document_no, cpny_id, department_id, vendorid, vendorname, keperluan, spkstartworkingdate as start_date, spkendtworkingdate as end_date, created_by, status")
            ->where('potype', 'SPK')->whereNull('deleted_at')->whereIn('status', ['P', 'C']);
        $contract = TrKontrak::query()->selectRaw("id as source_id, 'KONTRAK' as source_type, kontrakid as document_no, cpny_id, department_id, vendorid, vendorname, keperluan, startdate as start_date, enddate as end_date, created_by, status")
            ->whereNull('deleted_at')->whereIn('status', ['P', 'C']);

        return DB::connection('pgsql')->query()->fromSub($po->unionAll($contract), 'jobs');
    }

    private function jobs()
    {
        return $this->sources()->whereNotExists(function ($query) {
            $query->selectRaw('1')->from('tr_vendor_perfomance as e')
                ->whereColumn('e.vendor_perfomance_type', 'jobs.source_type')
                ->whereColumn('e.ponbr', 'jobs.document_no')
                ->whereRaw('e.cpny_id IS NOT DISTINCT FROM jobs.cpny_id')
                ->whereNull('e.deleted_at')->where('e.status', '<>', 'R');
        });
    }

    private function evaluations()
    {
        return TrVendorPerfomance::query()->whereNull('deleted_at');
    }

    public function indexEvaluasi()
    {
        $counts = $this->evaluations()->selectRaw('status, count(*) AS total')->groupBy('status')->pluck('total', 'status');
        $stats = ['jobs' => $this->jobs()->count(), 'onprogress' => (int) ($counts['P'] ?? 0),
            'rejected' => (int) ($counts['R'] ?? 0), 'revised' => (int) ($counts['D'] ?? 0),
            'completed' => (int) ($counts['C'] ?? 0), 'all' => $counts->sum()];
        $vendors = $this->sources()->select('vendorname')->whereNotNull('vendorname')->distinct()->orderBy('vendorname')->pluck('vendorname');

        return view('pages.evaluasivendor.index', compact('stats', 'vendors'));
    }

    public function jsonEvaluasi(Request $request)
    {
        $filters = $request->validate([
            'scope' => ['nullable', Rule::in(['jobs', 'onprogress', 'rejected', 'revised', 'completed', 'all'])],
            'source_type' => ['nullable', Rule::in(['SPK', 'KONTRAK'])],
            'vendor' => 'nullable|string|max:255', 'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d'.($request->filled('start_date') ? '|after_or_equal:start_date' : ''),
        ]);
        $jobs = ($filters['scope'] ?? 'jobs') === 'jobs';
        $query = $jobs ? $this->jobs() : $this->evaluations();
        $status = ['onprogress' => 'P', 'rejected' => 'R', 'revised' => 'D', 'completed' => 'C'][$filters['scope'] ?? 'jobs'] ?? null;
        if ($status) {
            $query->where('status', $status);
        }
        $total = (clone $query)->count();
        $typeColumn = $jobs ? 'source_type' : 'vendor_perfomance_type';
        $documentColumn = $jobs ? 'document_no' : 'ponbr';
        $startColumn = $jobs ? 'start_date' : 'vendor_perfomance_start_date';
        $endColumn = $jobs ? 'end_date' : 'vendor_perfomance_end_date';
        if (!empty($filters['vendor'])) {
            $query->where('vendorname', $filters['vendor']);
        }
        if (!empty($filters['source_type'])) {
            $query->where($typeColumn, $filters['source_type']);
        }
        if (!empty($filters['start_date'])) {
            $query->whereDate($startColumn, '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->whereDate($endColumn, '<=', $filters['end_date']);
        }
        $search = mb_substr(trim((string) $request->input('search.value', '')), 0, 200);
        if ($search !== '') {
            $query->where(function ($q) use ($search, $documentColumn, $jobs) {
                foreach ([$documentColumn, 'cpny_id', 'department_id', 'vendorname', 'keperluan', 'created_by'] as $column) {
                    $q->orWhere($column, 'ilike', '%'.$search.'%');
                }
                if (!$jobs) {
                    $q->orWhere('vendor_perfomance_id', 'ilike', '%'.$search.'%');
                }
            });
        }
        $filtered = (clone $query)->count();
        $columns = [1 => $typeColumn, 2 => $documentColumn, 3 => 'cpny_id', 4 => 'department_id', 5 => 'vendorname',
            6 => 'keperluan', 7 => $startColumn, 8 => $endColumn, 9 => 'created_by', 10 => 'status'];
        $order = $columns[(int) $request->input('order.0.column', 2)] ?? $documentColumn;
        $direction = $request->input('order.0.dir') === 'asc' ? 'asc' : 'desc';
        $rows = $query->orderBy($order, $direction)->orderBy($jobs ? 'source_id' : 'id')
            ->offset(max(0, (int) $request->input('start', 0)))
            ->limit(min(100, max(1, (int) $request->input('length', 10))))->get();
        $rows = $rows->map(function ($row) use ($jobs) {
            return [
                'source_type' => $jobs ? $row->source_type : $row->vendor_perfomance_type,
                'document_no' => $jobs ? $row->document_no : $row->ponbr,
                'evaluation_no' => $jobs ? null : $row->vendor_perfomance_id,
                'cpny_id' => $row->cpny_id, 'department_id' => $row->department_id,
                'vendorname' => $row->vendorname, 'keperluan' => $row->keperluan,
                'start_date' => $jobs ? $row->start_date : $row->vendor_perfomance_start_date,
                'end_date' => $jobs ? $row->end_date : $row->vendor_perfomance_end_date,
                'created_by' => $row->created_by, 'status' => self::STATUSES[$row->status] ?? $row->status,
                'score' => $jobs ? null : $row->template_vpe_aspect_grandtotal_score,
                'url' => $jobs ? route('evaluasivendor.create', ['source_type' => $row->source_type, 'source_id' => $row->source_id])
                    : $this->showUrlEvaluasi($row->id),
            ];
        });

        return response()->json(['draw' => (int) $request->input('draw', 1), 'recordsTotal' => $total,
            'recordsFiltered' => $filtered, 'data' => $rows]);
    }

    private function source(string $type, int $id, bool $lock = false)
    {
        abort_unless(in_array($type, ['SPK', 'KONTRAK'], true), 404);
        $query = ($type === 'SPK' ? TrPO::query() : TrKontrak::query())
            ->whereNull('deleted_at')->whereIn('status', ['P', 'C']);
        if ($type === 'SPK') {
            $query->where('potype', 'SPK');
        }
        if ($lock) {
            $query->lockForUpdate();
        }
        return $query->findOrFail($id);
    }

    public function createEvaluasi(Request $request)
    {
        $data = $request->validate(['source_type' => ['required', Rule::in(['SPK', 'KONTRAK'])], 'source_id' => 'required|integer|min:1']);
        $source = $this->source($data['source_type'], $data['source_id']);
        $evaluation = new TrVendorPerfomance($this->sourceFields($source, $data['source_type']));
        $evaluation->vendor_perfomance_date = now()->toDateString();
        $evaluation->status = 'P';
        $templates = MsTemplateVendorPerfomance::query()->whereNull('cpny_id')->whereNull('department_id')
            ->whereNull('deleted_at')->where('status', 'A')->select('template_vpe_id', 'template_vpe_descr')
            ->distinct()->orderBy('template_vpe_descr')->get();
        return view('pages.evaluasivendor.form', ['evaluation' => $evaluation, 'templates' => $templates,
            'details' => collect(), 'sourceId' => $source->id, 'editable' => true, 'statuses' => self::STATUSES]);
    }

    private function sourceFields($source, string $type): array
    {
        return ['vendor_perfomance_type' => $type, 'ponbr' => $type === 'SPK' ? $source->ponbr : $source->kontrakid,
            'cpny_id' => $source->cpny_id, 'department_id' => $source->department_id, 'csid' => $source->csid,
            'sppbjktid' => $source->sppbjktid, 'user_peminta' => $source->user_peminta,
            'vendorid' => $source->vendorid, 'vendorname' => $source->vendorname, 'keperluan' => $source->keperluan,
            'vendor_perfomance_start_date' => $type === 'SPK' ? $source->getRawOriginal('spkstartworkingdate') : $source->startdate,
            'vendor_perfomance_end_date' => $type === 'SPK' ? $source->getRawOriginal('spkendtworkingdate') : $source->enddate];
    }

    private function master(string $template): array
    {
        $scope = fn ($query) => $query->where('template_vpe_id', $template)->whereNull('cpny_id')->whereNull('department_id')
            ->where('status', 'A')->whereNull('deleted_at');
        $aspects = $scope(MsTemplateVendorPerfomance::query())->orderBy('template_vpe_aspect_id')->get();
        abort_if($aspects->isEmpty(), 404, 'Template evaluasi tidak ditemukan.');
        $details = $scope(MsTemplateVendorPerfomanceDetail::query())->orderBy('template_vpe_subaspect_id')->get()->map(fn ($row) => [
            'template_vpe_aspect_id' => $row->template_vpe_aspect_id,
            'template_vpe_aspect_descr' => $row->template_vpe_aspect_descr,
            'template_vpe_subaspect_id' => $row->template_vpe_subaspect_id,
            'template_vpe_subaspect_descr' => $row->template_vpe_subaspect_descr,
            'template_vpe_subaspect_kriteria' => $row->template_vpe_subaspect_kriteria,
            'template_vpe_subaspect_note' => $row->template_vpe_subaspect_note,
            'template_vpe_subaspect_bobot' => $row->template_vpe_aspect_bobot,
        ])->all();
        $scale = $scope(MsTemplateVendorPerfomanceScore::query())->get()->mapWithKeys(fn ($row) => [
            $row->template_vpe_aspect_id.':'.(int) $row->template_vpe_aspect_score => (float) $row->template_vpe_aspect_nilai,
        ])->all();
        if (!$details || abs(array_sum(array_column($details, 'template_vpe_subaspect_bobot')) - 100) > 0.001) {
            throw ValidationException::withMessages(['template_vpe_id' => 'Bobot master template harus berjumlah 100%.']);
        }
        return ['description' => $aspects->first()->template_vpe_descr, 'details' => $details, 'scale' => $scale];
    }

    public function templateEvaluasi(string $template)
    {
        return response()->json($this->master($template));
    }

    private function validated(Request $request, bool $creating): array
    {
        return $request->validate(array_merge([
            'vendor_perfomance_date' => 'required|date_format:Y-m-d',
            'vendor_perfomance_start_date' => 'required|date_format:Y-m-d',
            'vendor_perfomance_end_date' => 'required|date_format:Y-m-d|after_or_equal:vendor_perfomance_start_date',
            'vendor_perfomance_note' => 'nullable|string|max:10000',
            'template_vpe_aspect_comment' => 'nullable|string|max:10000',
            'ratings' => 'required|array', 'ratings.*' => 'required|integer|between:1,5',
            'notes' => 'nullable|array', 'notes.*' => 'nullable|string|max:10000',
        ], $creating ? ['source_type' => ['required', Rule::in(['SPK', 'KONTRAK'])],
            'source_id' => 'required|integer|min:1', 'template_vpe_id' => 'required|string|max:50'] : []), [
            'template_vpe_id.required' => 'Jenis Pekerjaan wajib dipilih.',
            'ratings.required' => 'Seluruh skor penilaian wajib diisi.',
            'ratings.*.required' => 'Skor pada setiap subaspek wajib diisi.',
            'ratings.*.integer' => 'Skor harus berupa angka 1 sampai 5.',
            'ratings.*.between' => 'Skor harus antara 1 sampai 5.',
        ]);
    }

    public function storeEvaluasi(Request $request, VendorEvaluationScoring $scoring)
    {
        $data = $this->validated($request, true);
        $doctype = 'EVO';
        $username = $request->user()->username;
        $dt = Carbon::now('Asia/Jakarta');
        $year = (int) $dt->year;
        $month = $dt->format('m');
        /** @var ApprovalController $approvalCtl */
        $approvalCtl = app(ApprovalController::class);

        // Autonbr and TrApproval use pgsql2; keep its locks and writes transactional too.
        $id = DB::connection('pgsql2')->transaction(function () use ($data, $request, $scoring, $doctype, $username, $dt, $year, $month, $approvalCtl) {
            return DB::connection('pgsql')->transaction(function () use ($data, $request, $scoring, $doctype, $username, $dt, $year, $month, $approvalCtl) {
                $source = $this->source($data['source_type'], $data['source_id'], true);
                $fields = $this->sourceFields($source, $data['source_type']);
                $duplicate = $this->evaluations()->where('vendor_perfomance_type', $fields['vendor_perfomance_type'])
                    ->where('ponbr', $fields['ponbr'])->where('cpny_id', $fields['cpny_id'])->where('status', '<>', 'R')->exists();
                if ($duplicate) {
                    throw ValidationException::withMessages(['source_id' => 'Pekerjaan ini sudah memiliki penilaian vendor.']);
                }
                $master = $this->master($data['template_vpe_id']);
                $result = $scoring->calculate($master['details'], $data['ratings'], $master['scale']);
                foreach ($result['details'] as &$detail) {
                    $detail['template_vpe_subaspect_note'] = $data['notes'][$detail['template_vpe_subaspect_id']] ?? $detail['template_vpe_subaspect_note'];
                }
                unset($detail);
                $approvalCtl->loadLines($doctype, $source->cpny_id, $source->department_id);

                // Also serialize the first allocation, when no EVO counter row exists yet.
                DB::connection('pgsql2')->select('SELECT pg_advisory_xact_lock(hashtext(?))', ["autonbr:{$doctype}:{$year}:{$month}"]);
                $auto = $this->nextAutonbr($doctype, $year, $month, $username, 'Evaluasi Vendor');
                $docid = $doctype.substr((string) $year, 2).$month.sprintf('%04d', (int) $auto['next']);
                $evaluation = new TrVendorPerfomance(array_merge($fields, $this->inputFields($data), [
                    'vendor_perfomance_id' => $docid,
                    'template_vpe_id' => $data['template_vpe_id'], 'template_vpe_descr' => $master['description'],
                    'template_vpe_aspect_grandtotal_score' => $result['total'], 'template_vpe_aspect_result' => $result['result'],
                    'status' => 'P', 'created_by' => $request->user()->username, 'updated_by' => $request->user()->username,
                ]));
                $evaluation->save();
                $this->saveDetails($evaluation, $result['details'], $request->user()->username);
                $approvalCtl->generateForDocument(
                    $docid,
                    $doctype,
                    $source->cpny_id,
                    $source->department_id,
                    $username,
                    ['ignore_nominal' => true],
                    $dt
                );
                $this->notifyApprovalEvaluasi($approvalCtl, $evaluation, $doctype, $dt);
                return $evaluation->id;
            });
        });
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Penilaian vendor berhasil diajukan untuk approval.',
                'redirect' => $this->showUrlEvaluasi($id)]);
        }
        return redirect($this->showUrlEvaluasi($id))->with('success', 'Penilaian vendor berhasil diajukan untuk approval.');
    }

    private function inputFields(array $data): array
    {
        return array_intersect_key($data, array_flip(['vendor_perfomance_date', 'vendor_perfomance_start_date',
            'vendor_perfomance_end_date', 'vendor_perfomance_note', 'template_vpe_aspect_comment']));
    }

    private function detailQuery(TrVendorPerfomance $evaluation)
    {
        return TrVendorPerfomanceDetail::query()->where('vendor_perfomance_id', $evaluation->vendor_perfomance_id)
            ->where('cpny_id', $evaluation->cpny_id)->where('ponbr', $evaluation->ponbr)
            ->where('vendor_perfomance_type', $evaluation->vendor_perfomance_type);
    }

    private function saveDetails(TrVendorPerfomance $evaluation, array $details, string $username): void
    {
        foreach ($details as $detail) {
            TrVendorPerfomanceDetail::create(array_merge($detail, [
                'vendor_perfomance_id' => $evaluation->vendor_perfomance_id,
                'vendor_perfomance_type' => $evaluation->vendor_perfomance_type,
                'ponbr' => $evaluation->ponbr, 'cpny_id' => $evaluation->cpny_id,
                'template_vpe_id' => $evaluation->template_vpe_id, 'status' => $evaluation->status,
                'created_by' => $username, 'updated_by' => $username,
            ]));
        }
    }

    public function legacyShowEvaluasi(int $id)
    {
        $this->evaluations()->findOrFail($id);
        return redirect($this->showUrlEvaluasi($id));
    }

    public function showEvaluasi(string $hash)
    {
        $decoded = Hashids::decode($hash);
        abort_unless(count($decoded) === 1, 404);
        $id = (int) $decoded[0];
        $evaluation = $this->evaluations()->findOrFail($id);
        $details = $this->detailQuery($evaluation)->orderBy('template_vpe_subaspect_id')->get();
        $approvals = TrApproval::query()->where('refnbr', $evaluation->vendor_perfomance_id)
            ->where('aprv_doctype', 'EVO')->where('aprv_cpnyid', $evaluation->cpny_id)
            ->where('aprv_departementid', $evaluation->department_id)
            ->orderByRaw('CAST(aprv_leveling AS numeric) ASC')->get();

        $active = $approvals->where('status', 'P')->firstWhere('aprv_datebefore', '!=', null)
            ?? $approvals->where('status', 'P')->first();
        $isApprover = $evaluation->status === 'P' && $active
            && in_array(strtolower(request()->user()->username), app(ApprovalController::class)->normalizeApproverList($active->aprv_username), true);
        $isSpk = $evaluation->vendor_perfomance_type === 'SPK';
        $sourceId = ($isSpk ? TrPO::query()->where('potype', 'SPK')->where('ponbr', $evaluation->ponbr)
            : TrKontrak::query()->where('kontrakid', $evaluation->ponbr))
            ->where('cpny_id', $evaluation->cpny_id)->whereNull('deleted_at')->value('id');
        $sourceUrl = $sourceId ? url(($isSpk ? '/showpo/' : '/showkontrak/').Hashids::encode($sourceId)) : null;
        return view('pages.evaluasivendor.showevaluasivendor', [
            'evaluation' => $evaluation, 'details' => $details, 'approvals' => $approvals,
            'statuses' => self::STATUSES, 'isApprover' => $isApprover, 'hash' => $hash,
            'sourceUrl' => $sourceUrl, 'sourceLabel' => $isSpk ? 'ponbr' : 'Kontrak No',
        ]);
    }

    public function editEvaluasi(int $id)
    {
        $evaluation = $this->evaluations()->findOrFail($id);
        abort_unless(in_array($evaluation->status, ['P', 'D']), 409, 'Penilaian sudah ditutup dan tidak dapat diubah.');
        $details = $this->detailQuery($evaluation)->orderBy('template_vpe_subaspect_id')->get();
        return view('pages.evaluasivendor.form', ['evaluation' => $evaluation, 'details' => $details,
            'templates' => collect(), 'sourceId' => null, 'editable' => in_array($evaluation->status, ['P', 'D']), 'statuses' => self::STATUSES]);
    }

    public function printEvaluasi(string $hash)
    {
        $evaluation = $this->commentDocumentEvaluasi($hash);
        $details = $this->detailQuery($evaluation)->orderBy('template_vpe_subaspect_id')->get();
        $approvals = TrApproval::query()->where('refnbr', $evaluation->vendor_perfomance_id)
            ->where('aprv_doctype', 'EVO')->where('aprv_cpnyid', $evaluation->cpny_id)
            ->where('aprv_departementid', $evaluation->department_id)->where('status', '<>', 'X')
            ->orderBy('created_at')->orderByRaw('CAST(aprv_leveling AS numeric) ASC')->orderBy('id')->get();
        $creatorName = User::query()->where('username', $evaluation->created_by)->value('name') ?: $evaluation->created_by;
        $templates = json_decode(file_get_contents(database_path('data/vendor_evaluation_templates.json')), true, 512, JSON_THROW_ON_ERROR);
        $guide = collect($templates)->firstWhere('id', $evaluation->template_vpe_id);

        return \PDF::loadView('pages.evaluasivendor.pdf_evaluasivendor', [
            'evaluation' => $evaluation, 'details' => $details, 'approvals' => $approvals,
            'creatorName' => $creatorName, 'guide' => $guide, 'statuses' => self::STATUSES,
        ])->setPaper('A4', 'portrait')->stream('Evaluasi_Vendor_'.$evaluation->vendor_perfomance_id.'.pdf');
    }

    private function commentDocumentEvaluasi(string $hash): TrVendorPerfomance
    {
        $decoded = Hashids::decode($hash);
        abort_unless(count($decoded) === 1, 404);
        return $this->evaluations()->findOrFail((int) $decoded[0]);
    }

    public function commentsEvaluasi(string $hash)
    {
        $evaluation = $this->commentDocumentEvaluasi($hash);
        return app(SendCommentController::class)->fetchComments('EVO', $evaluation->vendor_perfomance_id);
    }

    public function storeCommentEvaluasi(Request $request, string $hash)
    {
        $evaluation = $this->commentDocumentEvaluasi($hash);
        $request->validate(['comment' => 'required|string|max:500']);
        return app(SendCommentController::class)->storeComment($request, 'EVO', $evaluation->vendor_perfomance_id);
    }

    public function updateEvaluasi(Request $request, int $id, VendorEvaluationScoring $scoring)
    {
        $data = $this->validated($request, false);
        $doctype = 'EVO';
        $username = $request->user()->username;
        $dt = Carbon::now('Asia/Jakarta');
        /** @var ApprovalController $approvalCtl */
        $approvalCtl = app(ApprovalController::class);
        DB::connection('pgsql2')->transaction(function () use ($id, $data, $scoring, $request, $doctype, $username, $dt, $approvalCtl) {
            DB::connection('pgsql')->transaction(function () use ($id, $data, $scoring, $request, $doctype, $username, $dt, $approvalCtl) {
                $evaluation = $this->evaluations()->lockForUpdate()->findOrFail($id);
                abort_unless(in_array($evaluation->status, ['P', 'D']), 409, 'Penilaian sudah ditutup dan tidak dapat diubah.');
                $approvalCtl->loadLines($doctype, $evaluation->cpny_id, $evaluation->department_id);
                // Preserve the recorded criteria/weights; only the ratings and header inputs change.
                $details = $this->detailQuery($evaluation)->get();
                $master = $this->master($evaluation->template_vpe_id);
                $result = $scoring->calculate($details->toArray(), $data['ratings'], $master['scale']);
                $evaluation->fill(array_merge($this->inputFields($data), ['status' => 'P',
                    'template_vpe_aspect_grandtotal_score' => $result['total'], 'template_vpe_aspect_result' => $result['result'],
                    'updated_by' => $request->user()->username]))->save();
                foreach ($result['details'] as $detail) {
                    $this->detailQuery($evaluation)->where('id', $detail['id'])->update([
                        'template_vpe_subaspect_score' => $detail['template_vpe_subaspect_score'],
                        'template_vpe_subaspect_nilai' => $detail['template_vpe_subaspect_nilai'],
                        'template_vpe_subaspect_note' => $data['notes'][$detail['template_vpe_subaspect_id']] ?? $detail['template_vpe_subaspect_note'],
                        'status' => 'P', 'updated_by' => $request->user()->username, 'updated_at' => now(),
                    ]);
                }
                $approvalCtl->generateForDocument(
                    $evaluation->vendor_perfomance_id,
                    $doctype,
                    $evaluation->cpny_id,
                    $evaluation->department_id,
                    $username,
                    ['ignore_nominal' => true],
                    $dt
                );
                $this->notifyApprovalEvaluasi($approvalCtl, $evaluation, $doctype, $dt);
            });
        });
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Penilaian vendor berhasil diperbarui dan diajukan untuk approval.',
                'redirect' => $this->showUrlEvaluasi($id)]);
        }
        return redirect($this->showUrlEvaluasi($id))->with('success', 'Penilaian vendor berhasil diperbarui dan diajukan untuk approval.');
    }

    private function notifyApprovalEvaluasi(ApprovalController $approvalCtl, TrVendorPerfomance $evaluation, string $doctype, Carbon $dt): void
    {
        $approvalCtl->notifyFirstApprover(
            $evaluation->vendor_perfomance_id,
            $doctype,
            $evaluation->status,
            'Evaluasi Vendor',
            $this->showUrlEvaluasi($evaluation->id),
            [
                'info' => $evaluation->keperluan,
                'createdby' => $evaluation->created_by,
                'date' => $dt->toDateTimeString(),
            ]
        );
    }

    public function statusEvaluasi(Request $request, int $id)
    {
        $data = $request->validate(['status' => ['required', Rule::in(['C', 'R', 'D'])],
            'comment' => 'required_if:status,R,D|nullable|string|max:10000']);
        return $this->processApprovalEvaluasi($request, $id, $data['status'], $data['comment'] ?? null);
    }

    public function approvalEvaluasi(Request $request, string $hash, string $action)
    {
        $decoded = Hashids::decode($hash);
        abort_unless(count($decoded) === 1, 404);
        $status = ['approve' => 'C', 'revise' => 'D', 'reject' => 'R'][$action] ?? null;
        abort_unless($status, 404);
        $data = $request->validate(['comment' => ($status === 'C' ? 'nullable' : 'required').'|string|max:10000']);
        return $this->processApprovalEvaluasi($request, (int) $decoded[0], $status, $data['comment'] ?? null);
    }

    private function processApprovalEvaluasi(Request $request, int $id, string $status, ?string $comment)
    {
        DB::connection('pgsql2')->transaction(function () use ($id, $status, $comment, $request) {
            DB::connection('pgsql')->transaction(function () use ($id, $status, $comment, $request) {
                $evaluation = $this->evaluations()->lockForUpdate()->findOrFail($id);
                abort_unless($evaluation->status === 'P', 409, 'Hanya penilaian On Progress yang dapat diproses.');
                $ctl = app(ApprovalController::class);
                $action = ['C' => 'approve', 'D' => 'revise', 'R' => 'reject'][$status];
                [$allowed, , $message] = $ctl->assertUserCanAct($evaluation->vendor_perfomance_id, 'EVO', $action, $request->user()->username);
                abort_unless($allowed, 403, $message ?: 'Anda bukan approver aktif.');
                $apply = function () use ($evaluation, $status, $comment, $request) {
                    $evaluation->fill(['status' => $status, 'updated_by' => $request->user()->username,
                        'template_vpe_aspect_comment' => $comment ?? $evaluation->template_vpe_aspect_comment])->save();
                    $this->detailQuery($evaluation)->update(['status' => $status, 'updated_by' => $request->user()->username, 'updated_at' => now()]);
                };
                $args = [$evaluation->vendor_perfomance_id, 'EVO', $request->user()->username, $request->user()->name ?: $request->user()->username];
                $result = match ($status) {
                    'C' => $ctl->approveStep(...[...$args, $apply, function () use ($ctl, $evaluation) {
                        $this->notifyApprovalEvaluasi($ctl, $evaluation, 'EVO', Carbon::now('Asia/Jakarta'));
                    }]),
                    'D' => $ctl->reviseStep(...[...$args, $apply]),
                    'R' => $ctl->rejectStep(...[...$args, $apply]),
                };
                if (!$result['ok']) {
                    throw ValidationException::withMessages(['approval' => $result['message'] ?? 'Approval gagal diproses.']);
                }
            });
        });
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Proses approval berhasil.', 'redirect' => $this->showUrlEvaluasi($id)]);
        }
        return redirect($this->showUrlEvaluasi($id))->with('success', 'Proses approval berhasil.');
    }
}
