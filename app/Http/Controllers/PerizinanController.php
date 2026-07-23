<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\HasAutonbr;
use App\Models\DepartmentFin;
use App\Models\MsPerizinanCategory;
use App\Models\MsSite;
use App\Models\TrPerizinan;
use App\Models\TrPerizinanActivity;
use App\Models\TrPerizinanDetail;
use App\Models\User;
use App\Models\Usercpny;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PerizinanController extends Controller
{
    use HasAutonbr;

    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $today = now()->startOfDay();
        $reminderLimit = $today->copy()->addDays(30);

        $allPerizinan = TrPerizinan::query()->count();
        $activePerizinan = TrPerizinan::query()
            ->where(function ($query) use ($today) {
                $query->where('expired_date', false)
                    ->orWhereNull('expired_date')
                    ->orWhereDate('enddate', '>=', $today);
            })
            ->where(fn ($query) => $query->whereNotIn('status', ['C', 'R', 'X'])->orWhereNull('status'))
            ->count();
        $expiringPerizinan = TrPerizinan::query()
            ->where('expired_date', true)
            ->whereBetween('enddate', [$today, $reminderLimit])
            ->where(fn ($query) => $query->whereNotIn('status', ['C', 'R', 'X'])->orWhereNull('status'))
            ->count();
        $expiredPerizinan = TrPerizinan::query()
            ->where('expired_date', true)
            ->whereDate('enddate', '<', $today)
            ->where(fn ($query) => $query->whereNotIn('status', ['C', 'R', 'X'])->orWhereNull('status'))
            ->count();
        $completedPerizinan = TrPerizinan::query()->where('status', 'C')->count();

        $companies = Usercpny::query()->where('username', Auth::user()->username)
            ->where('status', 'A')->orderBy('cpny_id')->pluck('cpny_id')->unique()->values();
        $categories = MsPerizinanCategory::query()->where('status', 'A')
            ->orderBy('perizinancategory_descr')
            ->get(['perizinan_category', 'perizinancategory_descr']);
        $approvers = User::query()->where('status', 'A')->orderBy('name')->get(['username', 'name']);
        $expiryPeriods = TrPerizinan::query()
            ->whereNotNull('enddate')
            ->selectRaw('EXTRACT(YEAR FROM enddate)::int AS year, EXTRACT(MONTH FROM enddate)::int AS month')
            ->distinct()
            ->orderByRaw('EXTRACT(YEAR FROM enddate)::int DESC')
            ->orderByRaw('EXTRACT(MONTH FROM enddate)::int ASC')
            ->get();

        return view('pages.perizinan.perizinan', compact(
            'allPerizinan',
            'activePerizinan',
            'expiringPerizinan',
            'expiredPerizinan',
            'completedPerizinan', 'companies', 'categories', 'approvers', 'expiryPeriods'
        ));
    }

    public function json(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $draw = (int) $request->input('draw', 1);
        $start = max((int) $request->input('start', 0), 0);
        $length = min(max((int) $request->input('length', 25), 1), 100);
        $search = trim((string) $request->input('search.value', ''));
        $filter = strtolower((string) $request->input('filter', 'all'));
        $expiryYear = (int) $request->input('expiry_year', 0);
        $expiryMonth = (int) $request->input('expiry_month', 0);
        $category = trim((string) $request->input('category', ''));
        $today = now()->startOfDay();

        $query = TrPerizinan::query()->with(['site', 'category', 'latestActivity']);

        if ($filter === 'active') {
            $query->where(function ($subQuery) use ($today) {
                $subQuery->where('expired_date', false)
                    ->orWhereNull('expired_date')
                    ->orWhereDate('enddate', '>=', $today);
            })
                ->where(fn ($subQuery) => $subQuery->whereNotIn('status', ['C', 'R', 'X'])->orWhereNull('status'));
        } elseif ($filter === 'expiring') {
            $query->where('expired_date', true)
                ->whereBetween('enddate', [$today, $today->copy()->addDays(30)])
                ->where(fn ($subQuery) => $subQuery->whereNotIn('status', ['C', 'R', 'X'])->orWhereNull('status'));
        } elseif ($filter === 'expired') {
            $query->where('expired_date', true)
                ->whereDate('enddate', '<', $today)
                ->where(fn ($subQuery) => $subQuery->whereNotIn('status', ['C', 'R', 'X'])->orWhereNull('status'));
        } elseif ($filter === 'completed') {
            $query->where('status', 'C');
        }

        if ($expiryYear > 0) {
            $query->whereYear('enddate', $expiryYear);
        }
        if ($expiryMonth >= 1 && $expiryMonth <= 12) {
            $query->whereMonth('enddate', $expiryMonth);
        }
        if ($category !== '') {
            $query->where('perizinan_category', $category);
        }

        $recordsTotal = (clone $query)->count();

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('perizinan_id', 'ilike', "%{$search}%")
                    ->orWhere('perizinan_title', 'ilike', "%{$search}%")
                    ->orWhere('perizinan_category', 'ilike', "%{$search}%")
                    ->orWhere('cpny_id', 'ilike', "%{$search}%")
                    ->orWhere('site_id', 'ilike', "%{$search}%")
                    ->orWhere('department_fin_id', 'ilike', "%{$search}%")
                    ->orWhere('issuing_authority', 'ilike', "%{$search}%")
                    ->orWhere('vendor_name', 'ilike', "%{$search}%")
                    ->orWhere('status', 'ilike', "%{$search}%");
            });
        }

        $recordsFiltered = (clone $query)->count();
        $columns = [
            2 => 'perizinan_id',
            3 => 'cpny_id',
            4 => 'perizinan_category',
            5 => 'perizinan_title',
            6 => 'perizinan_descr',
            7 => 'startdate',
            8 => 'enddate',
            9 => 'status',
        ];
        $orderColumn = $columns[(int) $request->input('order.0.column', 8)] ?? 'enddate';
        $orderDirection = strtolower((string) $request->input('order.0.dir', 'asc')) === 'desc'
            ? 'desc'
            : 'asc';

        $data = $query->orderBy($orderColumn, $orderDirection)
            ->orderBy('id')
            ->skip($start)
            ->take($length)
            ->get([
                'id', 'perizinan_id', 'renewal_sequence', 'perizinan_date', 'cpny_id',
                'site_id', 'department_fin_id', 'user_peminta', 'perizinan_category',
                'perizinan_title', 'perizinan_descr', 'vendor_name',
                'startdate', 'enddate', 'reminder_date', 'expired_date', 'status', 'created_by',
            ])->map(function ($permit) {
                $permit->site_name = $permit->site?->site_name;
                $permit->category_name = $permit->category?->perizinancategory_descr;
                $permit->information = $permit->latestActivity?->response_descr;
                unset($permit->site, $permit->category, $permit->latestActivity);
                return $permit;
            });

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function departments(Request $request)
    {
        $allowedCompanies = Usercpny::query()->where('username', $request->user()->username)
            ->where('status', 'A')->pluck('cpny_id');
        $companyId = trim((string) $request->query('cpny_id'));

        abort_unless($allowedCompanies->contains($companyId), 403);

        return response()->json(DepartmentFin::query()
            ->where('cpny_id', $companyId)
            ->where('status', 'A')->orderBy('department_name')
            ->get(['department_fin_id', 'department_name']));
    }

    public function sites(Request $request)
    {
        $allowedCompanies = Usercpny::query()->where('username', $request->user()->username)
            ->where('status', 'A')->pluck('cpny_id');
        $companyId = trim((string) $request->query('cpny_id'));

        abort_unless($allowedCompanies->contains($companyId), 403);

        return response()->json(MsSite::query()
            ->where('cpny_id', $companyId)
            ->where('status', 'A')->orderBy('site_name')
            ->get(['siteid', 'site_name']));
    }

    public function edit(string $perizinanId)
    {
        $companyIds = Usercpny::query()->where('username', Auth::user()->username)
            ->where('status', 'A')->pluck('cpny_id');
        $perizinan = TrPerizinan::query()->where('perizinan_id', $perizinanId)
            ->whereIn('cpny_id', $companyIds)->firstOrFail();
        $details = TrPerizinanDetail::query()->where('perizinan_id', $perizinanId)
            ->orderBy('id')->get(['id', 'item_perizinan', 'qty_perizinan']);

        return response()->json(['data' => $perizinan, 'details' => $details]);
    }

    public function show(string $perizinanId)
    {
        $companyIds = Usercpny::query()->where('username', Auth::user()->username)
            ->where('status', 'A')->pluck('cpny_id');
        $permit = TrPerizinan::query()
            ->with([
                'site',
                'category',
                'department',
                'details' => fn ($query) => $query->orderBy('id'),
                'activities' => fn ($query) => $query->where('status', 'A')->orderBy('response_date')->orderBy('id'),
            ])
            ->where('perizinan_id', $perizinanId)
            ->whereIn('cpny_id', $companyIds)
            ->firstOrFail();

        return response()->json(['data' => $permit]);
    }

    public function storeActivity(Request $request, string $perizinanId)
    {
        $validated = $request->validate([
            'response_descr' => ['required', 'string'],
            'status_pekerjaan' => ['required', Rule::in(['WAITING', 'PROCESS', 'REJECTED', 'CANCELLED', 'DONE'])],
        ]);
        $companyIds = Usercpny::query()->where('username', $request->user()->username)
            ->where('status', 'A')->pluck('cpny_id');
        $activity = DB::connection('pgsql')->transaction(function () use (
            $companyIds,
            $perizinanId,
            $request,
            $validated
        ) {
            $permit = TrPerizinan::query()->where('perizinan_id', $perizinanId)
                ->whereIn('cpny_id', $companyIds)
                ->lockForUpdate()
                ->firstOrFail();

            $activity = TrPerizinanActivity::create([
                'perizinan_id' => $perizinanId,
                'pic_perizinan' => $request->user()->username,
                'response_date' => now(),
                'response_descr' => $validated['response_descr'],
                'status_pekerjaan' => $validated['status_pekerjaan'],
                'status' => 'A',
                'created_by' => $request->user()->username,
            ]);

            $permitStatus = [
                'DONE' => 'C',
                'REJECTED' => 'R',
                'CANCELLED' => 'X',
            ][$validated['status_pekerjaan']] ?? null;

            if ($permitStatus) {
                $permit->status = $permitStatus;
                $permit->updated_by = $request->user()->username;
                if ($permitStatus === 'C') {
                    $permit->completed_by = $request->user()->username;
                    $permit->completed_at = now();
                }
                $permit->save();
            }

            return $activity;
        });

        return response()->json([
            'message' => 'Permit activity saved successfully.',
            'data' => $activity,
        ]);
    }

    public function renew(Request $request, string $perizinanId)
    {
        $user = $request->user();
        $companyIds = Usercpny::query()->where('username', $user->username)
            ->where('status', 'A')->pluck('cpny_id');

        DB::connection('pgsql')->beginTransaction();
        try {
            $source = TrPerizinan::query()
                ->with('details')
                ->where('perizinan_id', $perizinanId)
                ->whereIn('cpny_id', $companyIds)
                ->lockForUpdate()
                ->firstOrFail();

            $now = now();
            $year = (int) $now->year;
            $month = str_pad((string) $now->month, 2, '0', STR_PAD_LEFT);
            $auto = $this->nextAutonbr('MIK', $year, $month, $user->username, 'Permit & Compliance Monitoring');
            $newPermitId = 'MIK'.substr((string) $year, 2).$month.sprintf('%04d', (int) $auto['next']);

            $renewal = $source->replicate([
                'id', 'perizinan_id', 'perizinan_date', 'prev_perizinan_id',
                'renewal_sequence', 'startdate', 'enddate', 'created_at', 'updated_at',
                'created_by', 'updated_by', 'completed_by', 'completed_at',
                'deleted_by', 'deleted_at',
            ]);
            $renewal->perizinan_id = $newPermitId;
            $renewal->perizinan_date = $now->toDateString();
            $renewal->prev_perizinan_id = $source->perizinan_id;
            $renewal->renewal_sequence = ((int) $source->renewal_sequence) + 1;
            $renewal->startdate = null;
            $renewal->enddate = null;
            $renewal->status = 'P';
            $renewal->created_by = $user->username;
            $renewal->updated_by = null;
            $renewal->completed_by = null;
            $renewal->completed_at = null;
            $renewal->save();

            foreach ($source->details as $sourceDetail) {
                $detail = $sourceDetail->replicate([
                    'id', 'perizinan_id', 'created_at', 'updated_at',
                    'created_by', 'updated_by', 'completed_by', 'completed_at',
                    'deleted_by', 'deleted_at',
                ]);
                $detail->perizinan_id = $newPermitId;
                $detail->status = 'P';
                $detail->created_by = $user->username;
                $detail->save();
            }

            DB::connection('pgsql')->commit();

            return response()->json([
                'message' => 'Renewal permit created successfully.',
                'perizinan_id' => $newPermitId,
            ]);
        } catch (\Throwable $exception) {
            DB::connection('pgsql')->rollBack();
            report($exception);

            return response()->json([
                'message' => 'Failed to create permit renewal.',
                'error' => $exception->getMessage(),
            ], 500);
        }
    }

    public function savePerizinan(Request $request, ?string $perizinanId = null)
    {
        $user = $request->user();
        $companyIds = Usercpny::query()->where('username', $user->username)
            ->where('status', 'A')->pluck('cpny_id')->all();

        $validated = $request->validate([
            'cpnyid' => ['required', Rule::in($companyIds)],
            'site_id' => ['required', Rule::exists('pgsql.ms_site', 'siteid')
                ->where(fn ($query) => $query->where('cpny_id', $request->input('cpnyid'))->where('status', 'A'))],
            'departementid' => ['required', Rule::exists('pgsql.ms_department_fin', 'department_fin_id')
                ->where(fn ($query) => $query->where('cpny_id', $request->input('cpnyid'))->where('status', 'A'))],
            'perizinan_category' => ['required', Rule::exists('pgsql.ms_perizinan_category', 'perizinan_category')->where('status', 'A')],
            'perizinan_title' => ['required', 'string', 'max:255'],
            'perizinan_descr' => ['nullable', 'string'],
            'startdate' => ['required', 'date'],
            'expired_date' => ['required', 'boolean'],
            'enddate' => ['nullable', 'required_if:expired_date,1', 'date', 'after_or_equal:startdate'],
            'user_approval' => ['required', 'array', 'min:1'],
            'user_approval.*' => ['required', 'string', Rule::exists('pgsql2.ms_user', 'username')->where('status', 'A')],
            'item_perizinan' => ['required', 'array', 'min:1'],
            'item_perizinan.*' => ['required', 'string', 'max:255'],
            'qty_perizinan' => ['required', 'array', 'min:1'],
            'qty_perizinan.*' => ['required', 'numeric', 'gt:0'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:5120'],
        ]);

        if (count($validated['item_perizinan']) !== count($validated['qty_perizinan'])) {
            throw ValidationException::withMessages([
                'item_perizinan' => 'The number of detail items and quantities does not match.',
            ]);
        }

        DB::connection('pgsql')->beginTransaction();
        try {
            $now = now();
            $doctype = 'MIK';
            $isEdit = $perizinanId !== null;

            if ($isEdit) {
                $header = TrPerizinan::query()->where('perizinan_id', $perizinanId)
                    ->whereIn('cpny_id', $companyIds)
                    ->lockForUpdate()->firstOrFail();
                $docid = $header->perizinan_id;
            } else {
                $year = (int) $now->year;
                $month = str_pad((string) $now->month, 2, '0', STR_PAD_LEFT);
                $auto = $this->nextAutonbr($doctype, $year, $month, $user->username, 'Permit & Compliance Monitoring');
                $docid = $doctype.substr((string) $year, 2).$month.sprintf('%04d', (int) $auto['next']);
                $header = new TrPerizinan();
                $header->perizinan_id = $docid;
                $header->perizinan_date = $now->toDateString();
                $header->renewal_sequence = 0;
                $header->status = 'P';
                $header->created_by = $user->username;
            }

            $header->cpny_id = $validated['cpnyid'];
            $header->site_id = $validated['site_id'];
            $header->department_fin_id = $validated['departementid'];
            $header->user_peminta = $user->username;
            $header->user_approval = implode(',', $validated['user_approval']);
            $header->perizinan_category = $validated['perizinan_category'];
            $header->perizinan_title = $validated['perizinan_title'];
            $header->perizinan_descr = $validated['perizinan_descr'] ?? null;
            $header->startdate = $validated['startdate'];
            $header->expired_date = (bool) $validated['expired_date'];
            $header->enddate = $header->expired_date ? $validated['enddate'] : null;
            $header->qty_item_perizinan = array_sum(array_map('floatval', $validated['qty_perizinan']));
            $header->status = 'P';
            if ($isEdit) $header->updated_by = $user->username;
            $header->save();

            if ($isEdit) TrPerizinanDetail::query()->where('perizinan_id', $docid)->delete();
            foreach ($validated['item_perizinan'] as $index => $item) {
                TrPerizinanDetail::create([
                    'perizinan_id' => $docid,
                    'item_perizinan' => $item,
                    'qty_perizinan' => $validated['qty_perizinan'][$index],
                    'status' => 'P',
                    'created_by' => $user->username,
                ]);
            }

            if (!$isEdit) {
                TrPerizinanActivity::create([
                    'perizinan_id' => $docid,
                    'pic_perizinan' => $user->username,
                    'response_date' => $now,
                    'response_descr' => 'Permit submitted.',
                    'status_pekerjaan' => 'SUBMITTED',
                    'status' => 'A',
                    'created_by' => $user->username,
                ]);
            }

            if ($request->hasFile('attachments')) {
                app(TrAttachmentController::class)->uploadInternal([
                    'refnbr' => $docid,
                    'doctype' => $doctype,
                    'cpny_id' => $validated['cpnyid'],
                    'department_id' => $validated['departementid'],
                    'base_folder' => 'att-purchasing-app/'.strtolower($doctype),
                    'created_by' => $user->username,
                ], (array) $request->file('attachments'));
            }

            DB::connection('pgsql')->commit();
            return response()->json([
                'message' => $isEdit ? 'Permit updated successfully.' : 'Permit created successfully.',
                'perizinan_id' => $docid,
                'redirect' => route('perizinan'),
            ]);
        } catch (\Throwable $exception) {
            DB::connection('pgsql')->rollBack();
            report($exception);
            return response()->json([
                'message' => $perizinanId ? 'Failed to update permit.' : 'Failed to create permit.',
                'error' => $exception->getMessage(),
            ], 500);
        }
    }
}
