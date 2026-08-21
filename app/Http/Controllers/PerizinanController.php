<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\HasAutonbr;
use App\Models\DepartmentFin;
use App\Models\MsDepartment;
use App\Models\MsPerizinanCategory;
use App\Models\MsSite;
use App\Models\SysUserRole;
use App\Models\TrPerizinan;
use App\Models\TrPerizinanActivity;
use App\Models\TrPerizinanDetail;
use App\Models\TrCS;
use App\Models\TrSPPJ;
use App\Models\User;
use App\Models\Usercpny;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Vinkla\Hashids\Facades\Hashids;

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
        $companies = Auth::user()->hasFullDataScope()
            ? \App\Models\MsCompany::orderBy('cpny_id')->pluck('cpny_id')
            : Usercpny::query()->where('username', Auth::user()->username)
                ->where('status', 'A')->orderBy('cpny_id')->pluck('cpny_id')->unique()->values();
        $access = $this->perizinanAccessScope(Auth::user());
        $hasGaAccess = $access['has_ga_access'];
        $hasUserPermitAccess = !$hasGaAccess && SysUserRole::query()
            ->where('username', Auth::user()->username)
            ->where('role_id', 'USERPERMITACCESS')
            ->where('status', 'A')
            ->exists();
        $permitQuery = function () use ($companies, $access) {
            $query = TrPerizinan::query()->whereIn('cpny_id', $companies);

            if (!$access['has_ga_access']) {
                $query->whereIn('department_fin_id', $access['department_fin_ids']);
            }

            return $query;
        };

        $allPerizinan = $permitQuery()->count();
        $activePerizinan = $permitQuery()
            ->where(function ($query) use ($today) {
                $query->where('expired_date', false)
                    ->orWhereNull('expired_date')
                    ->orWhereDate('enddate', '>=', $today);
            })
            ->where(fn ($query) => $query->whereNotIn('status', ['C', 'R', 'X'])->orWhereNull('status'))
            ->count();
        $expiringPerizinan = $permitQuery()
            ->where('expired_date', true)
            ->whereBetween('enddate', [$today, $reminderLimit])
            ->where(fn ($query) => $query->whereNotIn('status', ['C', 'R', 'X'])->orWhereNull('status'))
            ->count();
        $expiredPerizinan = $permitQuery()
            ->where('expired_date', true)
            ->whereDate('enddate', '<', $today)
            ->where(fn ($query) => $query->whereNotIn('status', ['C', 'R', 'X'])->orWhereNull('status'))
            ->count();
        $completedPerizinan = $permitQuery()
            ->where('status', 'C')
            ->count();
        $expiry30To60 = $permitQuery()
            ->where('expired_date', true)
            ->whereRaw('(enddate::date - CURRENT_DATE) BETWEEN 30 AND 60')
            ->whereNotIn('status', ['C', 'R', 'X'])
            ->count();
        $expiry60To90 = $permitQuery()
            ->where('expired_date', true)
            ->whereRaw('(enddate::date - CURRENT_DATE) BETWEEN 60 AND 90')
            ->whereNotIn('status', ['C', 'R', 'X'])
            ->count();
        $expiry90Plus = $permitQuery()
            ->where('expired_date', true)
            ->whereRaw('(enddate::date - CURRENT_DATE) >= 90')
            ->whereNotIn('status', ['C', 'R', 'X'])
            ->count();
        $categories = MsPerizinanCategory::query()->where('status', 'A')
            ->orderBy('perizinancategory_descr')
            ->get(['perizinan_category', 'perizinancategory_descr']);
        $sitesQuery = MsSite::query()->where('status', 'A')
            ->whereIn('cpny_id', $companies)
            ->orderBy('cpny_id')
            ->orderBy('site_name');
        if (!$access['has_ga_access']) {
            $sitesQuery->whereIn('siteid', $permitQuery()
                ->whereNotNull('site_id')
                ->select('site_id'));
        }
        $sites = $sitesQuery->get(['siteid', 'site_name', 'cpny_id']);
        $approvers = User::query()->where('status', 'A')->orderBy('name')->get(['username', 'name']);
        $expiryPeriods = $permitQuery()
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
            'completedPerizinan', 'expiry30To60', 'expiry60To90', 'expiry90Plus',
            'companies', 'categories', 'sites', 'approvers', 'expiryPeriods',
            'hasGaAccess', 'hasUserPermitAccess'
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
        $siteId = trim((string) $request->input('site_id', ''));
        $today = now()->startOfDay();
        $companyIds = Auth::user()->hasFullDataScope()
            ? \App\Models\MsCompany::pluck('cpny_id')
            : Usercpny::query()->where('username', Auth::user()->username)
                ->where('status', 'A')->pluck('cpny_id')->unique()->values();
        $access = $this->perizinanAccessScope(Auth::user());

        $query = TrPerizinan::query()
            ->with([
                'site',
                'category',
                'latestActivity',
                'renewals' => fn ($renewalQuery) => $renewalQuery
                    ->where(fn ($statusQuery) => $statusQuery
                        ->whereNotIn('status', ['X', 'R'])
                        ->orWhereNull('status'))
                    ->select(['id', 'prev_perizinan_id', 'status']),
            ])
            ->whereIn('cpny_id', $companyIds);

        if (!$access['has_ga_access']) {
            $query->whereIn('department_fin_id', $access['department_fin_ids']);
        }

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
        } elseif ($filter === 'expiry_30_60') {
            $query->where('expired_date', true)
                ->whereRaw('(enddate::date - CURRENT_DATE) BETWEEN 30 AND 60')
                ->whereNotIn('status', ['C', 'R', 'X']);
        } elseif ($filter === 'expiry_60_90') {
            $query->where('expired_date', true)
                ->whereRaw('(enddate::date - CURRENT_DATE) BETWEEN 60 AND 90')
                ->whereNotIn('status', ['C', 'R', 'X']);
        } elseif ($filter === 'expiry_90_plus') {
            $query->where('expired_date', true)
                ->whereRaw('(enddate::date - CURRENT_DATE) >= 90')
                ->whereNotIn('status', ['C', 'R', 'X']);
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
        if ($siteId !== '') {
            $query->where('site_id', $siteId);
        }

        $recordsTotal = (clone $query)->count();

        if ($search !== '') {
            $statusSearch = collect([
                'P' => 'on progress',
                'C' => 'completed',
                'R' => 'rejected',
                'X' => 'cancelled',
            ])->filter(fn ($label) => str_contains($label, strtolower($search)))->keys()->all();

            $query->where(function ($subQuery) use ($search, $statusSearch) {
                $subQuery->where('perizinan_id', 'ilike', "%{$search}%")
                    ->orWhere('perizinan_title', 'ilike', "%{$search}%")
                    ->orWhere('perizinan_descr', 'ilike', "%{$search}%")
                    ->orWhere('perizinan_category', 'ilike', "%{$search}%")
                    ->orWhere('cpny_id', 'ilike', "%{$search}%")
                    ->orWhere('site_id', 'ilike', "%{$search}%")
                    ->orWhere('department_fin_id', 'ilike', "%{$search}%")
                    ->orWhere('status', 'ilike', "%{$search}%")
                    ->orWhereRaw("TO_CHAR(startdate, 'FMMonth YYYY') ILIKE ?", ["%{$search}%"])
                    ->orWhereRaw("TO_CHAR(enddate, 'FMMonth YYYY') ILIKE ?", ["%{$search}%"])
                    ->orWhereHas('site', fn ($siteQuery) => $siteQuery
                        ->where('site_name', 'ilike', "%{$search}%"))
                    ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery
                        ->where('perizinancategory_descr', 'ilike', "%{$search}%"))
                    ->orWhereHas('latestActivity', fn ($activityQuery) => $activityQuery
                        ->where('response_descr', 'ilike', "%{$search}%"));

                if ($statusSearch) {
                    $subQuery->orWhereIn('status', $statusSearch);
                }
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
                $permit->detail_hash = Hashids::encode($permit->id);
                $permit->site_name = $permit->site?->site_name;
                $permit->category_name = $permit->category?->perizinancategory_descr;
                $permit->information = $permit->latestActivity?->response_descr;
                $permit->has_blocking_renewal = $permit->renewals->isNotEmpty();
                unset($permit->site, $permit->category, $permit->latestActivity, $permit->renewals);
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

        $permit->activities->each(function ($activity) use ($permit) {
            $activity->attachment_refnbr = $permit->perizinan_id;
            $activity->attachment_doctype = 'ACT-'.$activity->id;
        });

        $permit->sppbjkt_url = null;
        if (filled($permit->sppbjktid)) {
            $sppjId = TrSPPJ::query()
                ->where('sppjid', $permit->sppbjktid)
                ->value('id');

            if ($sppjId) {
                $permit->sppbjkt_url = url('/showsppjs/'.Hashids::encode($sppjId));
            }
        }

        $permit->cs_url = null;
        if (filled($permit->csid)) {
            $csId = TrCS::query()
                ->where('csid', $permit->csid)
                ->value('id');

            if ($csId) {
                $permit->cs_url = url('/showcs/'.Hashids::encode($csId));
            }
        }

        return response()->json(['data' => $permit]);
    }

    public function showPage(string $hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_unless($id, 404);

        $companyIds = Usercpny::query()->where('username', Auth::user()->username)
            ->where('status', 'A')
            ->pluck('cpny_id');

        $permit = TrPerizinan::query()
            ->whereKey($id)
            ->whereIn('cpny_id', $companyIds)
            ->firstOrFail(['id', 'perizinan_id']);

        return $this->index()->with('openPermitId', $permit->perizinan_id);
    }

    public function storeActivity(Request $request, string $perizinanId)
    {
        $validated = $request->validate([
            'response_descr' => ['required', 'string'],
            'status_pekerjaan' => ['required', Rule::in(['WAITING', 'PROCESS', 'REJECTED', 'CANCELLED', 'DONE'])],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:5120'],
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

            if ($request->hasFile('attachments')) {
                app(TrAttachmentController::class)->uploadInternal([
                    'refnbr' => $perizinanId,
                    'doctype' => 'ACT-'.$activity->id,
                    'cpny_id' => $permit->cpny_id,
                    'department_id' => $permit->department_fin_id,
                    'base_folder' => 'att-purchasing-app/mik',
                    'created_by' => $request->user()->username,
                ], (array) $request->file('attachments'));
            }

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

    public function updateDetails(Request $request, string $perizinanId)
    {
        $user = $request->user();
        $hasGaAccess = SysUserRole::query()
            ->where('username', $user->username)
            ->where('role_id', 'GAACCESS')
            ->where('status', 'A')
            ->exists();
        $hasUserPermitAccess = SysUserRole::query()
            ->where('username', $user->username)
            ->where('role_id', 'USERPERMITACCESS')
            ->where('status', 'A')
            ->exists();

        abort_unless($hasUserPermitAccess && !$hasGaAccess, 403, 'You are not allowed to update permit items.');

        $validated = $request->validate([
            'item_perizinan' => ['required', 'array', 'min:1'],
            'item_perizinan.*' => ['required', 'string', 'max:255'],
            'qty_perizinan' => ['required', 'array', 'min:1'],
            'qty_perizinan.*' => ['required', 'numeric', 'gt:0'],
        ]);

        if (count($validated['item_perizinan']) !== count($validated['qty_perizinan'])) {
            throw ValidationException::withMessages([
                'item_perizinan' => 'The number of permit items and quantities does not match.',
            ]);
        }

        $companyIds = $user->hasFullDataScope()
            ? \App\Models\MsCompany::pluck('cpny_id')
            : Usercpny::query()->where('username', $user->username)
                ->where('status', 'A')->pluck('cpny_id')->unique()->values();
        $access = $this->perizinanAccessScope($user);

        DB::connection('pgsql')->transaction(function () use (
            $access,
            $companyIds,
            $perizinanId,
            $user,
            $validated
        ) {
            $permit = TrPerizinan::query()
                ->where('perizinan_id', $perizinanId)
                ->whereIn('cpny_id', $companyIds)
                ->whereIn('department_fin_id', $access['department_fin_ids'])
                ->lockForUpdate()
                ->firstOrFail();

            if (strtoupper((string) $permit->status) === 'C') {
                throw ValidationException::withMessages([
                    'permit' => 'Completed permit items cannot be updated.',
                ]);
            }

            TrPerizinanDetail::query()->where('perizinan_id', $permit->perizinan_id)->delete();

            foreach ($validated['item_perizinan'] as $index => $item) {
                TrPerizinanDetail::create([
                    'perizinan_id' => $permit->perizinan_id,
                    'item_perizinan' => $item,
                    'qty_perizinan' => $validated['qty_perizinan'][$index],
                    'status' => 'P',
                    'created_by' => $user->username,
                ]);
            }

            $permit->qty_item_perizinan = array_sum(array_map('floatval', $validated['qty_perizinan']));
            $permit->updated_by = $user->username;
            $permit->save();
        });

        return response()->json(['message' => 'Permit items updated successfully.']);
    }

    private function sendSavedPermitEmail(string $perizinanId, bool $isEdit): bool
    {
        try {
            $permit = TrPerizinan::query()
                ->where('perizinan_id', $perizinanId)
                ->firstOrFail();

            $requester = User::query()
                ->where('username', $permit->user_peminta)
                ->where('status', 'A')
                ->first(['username', 'name', 'email', 'notification_email']);
            $to = trim((string) ($requester?->notification_email ?: $requester?->email));

            if (!$requester || $to === '') {
                logger()->warning('Permit save email skipped: requester email not found.', [
                    'perizinan_id' => $perizinanId,
                    'user_peminta' => $permit->user_peminta,
                ]);

                return false;
            }

            $ccUsernames = collect(explode(',', (string) $permit->user_dept_peminta))
                ->map(fn ($username) => trim($username))
                ->filter()
                ->unique()
                ->values();
            $ccEmails = User::query()
                ->whereIn('username', $ccUsernames)
                ->where('status', 'A')
                ->get(['email', 'notification_email'])
                ->map(fn ($user) => trim((string) ($user->notification_email ?: $user->email)))
                ->filter(fn ($email) => $email !== '' && strcasecmp($email, $to) !== 0)
                ->unique()
                ->values()
                ->all();

            $permitUrl = url('/showperizinan/'.Hashids::encode($permit->id));
            $data = [
                'recipientName' => $requester->name ?: $requester->username,
                'permit' => $permit,
                'permitUrl' => $permitUrl,
                'isEdit' => $isEdit,
            ];

            Mail::send('emails.perizinan-saved', $data, function ($message) use ($to, $ccEmails, $permit, $isEdit) {
                $message->to($to)
                    ->subject($permit->perizinan_id.($isEdit ? ' - Permit Updated' : ' - Permit Created'))
                    ->from(config('mail.from.address'), config('app.name'));

                if ($ccEmails !== []) {
                    $message->cc($ccEmails);
                }
            });

            return true;
        } catch (\Throwable $exception) {
            report($exception);
            logger()->error('Failed to send permit save email.', [
                'perizinan_id' => $perizinanId,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
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

            $hasBlockingRenewal = TrPerizinan::query()
                ->where('prev_perizinan_id', $source->perizinan_id)
                ->where(fn ($statusQuery) => $statusQuery
                    ->whereNotIn('status', ['X', 'R'])
                    ->orWhereNull('status'))
                ->lockForUpdate()
                ->first(['id']) !== null;

            if ($hasBlockingRenewal) {
                throw ValidationException::withMessages([
                    'renewal' => 'This permit has already been renewed.',
                ]);
            }

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
        } catch (ValidationException $exception) {
            DB::connection('pgsql')->rollBack();
            throw $exception;
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
            'reminder_days_before_end' => ['required', 'integer', Rule::in([120, 90, 60, 30])],
            'application_handling_method' => ['nullable', Rule::in(['INTERNAL', 'EXTERNAL'])],
            'issuing_authority' => ['nullable', 'string', 'max:255'],
            'submission_channel' => ['nullable', 'string', 'max:255'],
            'no_kontrak_legal' => ['nullable', 'string', 'max:255'],
            'issue_date' => ['nullable', 'date'],
            'user_dept_approval' => ['required', 'array', 'min:1'],
            'user_dept_approval.*' => ['required', 'string', Rule::exists('pgsql2.ms_user', 'username')->where('status', 'A')],
            'user_dept_peminta' => ['required', 'array', 'min:1'],
            'user_dept_peminta.*' => ['required', 'string', Rule::exists('pgsql2.ms_user', 'username')->where('status', 'A')],
            'sppbjktid' => ['nullable', 'string', 'max:255'],
            'csid' => ['nullable', 'string', 'max:255'],
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
            $header->user_dept_approval = implode(',', $validated['user_dept_approval']);
            $header->user_dept_peminta = implode(',', $validated['user_dept_peminta']);
            $header->perizinan_category = $validated['perizinan_category'];
            $header->perizinan_title = $validated['perizinan_title'];
            $header->perizinan_descr = $validated['perizinan_descr'] ?? null;
            $header->startdate = $validated['startdate'];
            $header->expired_date = (bool) $validated['expired_date'];
            $header->enddate = $header->expired_date ? $validated['enddate'] : null;
            $header->reminder_days_before_end = $validated['reminder_days_before_end'];
            $header->reminder_date = $header->expired_date
                ? Carbon::parse($validated['enddate'])->subDays((int) $validated['reminder_days_before_end'])->toDateString()
                : null;
            $header->application_handling_method = $validated['application_handling_method'] ?? null;
            $header->issuing_authority = $validated['issuing_authority'] ?? null;
            $header->submission_channel = $validated['submission_channel'] ?? null;
            $header->no_kontrak_legal = $validated['no_kontrak_legal'] ?? null;
            $header->sppbjktid = $validated['sppbjktid'] ?? null;
            $header->csid = $validated['csid'] ?? null;
            $header->issue_date = $validated['issue_date'] ?? null;
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
            $emailSent = $this->sendSavedPermitEmail($docid, $isEdit);

            return response()->json([
                'message' => $isEdit ? 'Permit updated successfully.' : 'Permit created successfully.',
                'perizinan_id' => $docid,
                'redirect' => route('perizinan'),
                'email_sent' => $emailSent,
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

    /**
     * Resolve permit visibility from the login user's operational departments.
     * GAACCESS sees every department inside the already-authorized company scope.
     */
    private function perizinanAccessScope(User $user): array
    {
        $hasGaAccess = SysUserRole::query()
            ->where('username', $user->username)
            ->where('role_id', 'GAACCESS')
            ->where('status', 'A')
            ->exists();

        if ($hasGaAccess) {
            return [
                'has_ga_access' => true,
                'department_fin_ids' => [],
            ];
        }

        $departmentIds = collect(preg_split('/\s*,\s*/', (string) $user->department_id))
            ->map(fn ($departmentId) => trim((string) $departmentId))
            ->filter()
            ->unique()
            ->values();

        $departmentFinIds = MsDepartment::query()
            ->whereIn('department_id', $departmentIds)
            ->where('status', 'A')
            ->whereNotNull('department_fin_id')
            ->pluck('department_fin_id')
            ->map(fn ($departmentFinId) => trim((string) $departmentFinId))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return [
            'has_ga_access' => false,
            'department_fin_ids' => $departmentFinIds,
        ];
    }
}
