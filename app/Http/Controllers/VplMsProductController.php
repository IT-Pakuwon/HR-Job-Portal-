<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Vinkla\Hashids\Facades\Hashids;
use Google\Cloud\Storage\StorageClient;
use App\Models\MsVplProduct;
use App\Models\MsVplProductBal;
use App\Models\MsVplProductDetail;
use App\Models\MsVplProductTargetDate;
use App\Models\MsVplWarehouse;
use App\Models\MsVplAging;
use App\Models\MsCategory;
use App\Models\MsBaseUom;
use App\Models\MsDepartment;
use App\Models\TrAttachment;
use App\Models\TrxVplTransferDetail;
use App\Models\TrxVplUsageDetail;
use App\Models\Usercpny;
use App\Http\Controllers\TrAttachmentController;
use DataTables;

class VplMsProductController extends Controller
{
    // Company → 1-digit prefix for product_id (e.g. V100001, P200001)
    // Update cpnyid keys to match your actual ms_company cpny_id values
    private const COMPANY_PREFIX = [
        'AW'  => '1',
        'EP'  => '2',
        'PSA' => '3',
        'GPS' => '4',
    ];

    // -------------------------------------------------------
    // HELPER
    // -------------------------------------------------------

    private function cpnyIds(): array
    {
        $user = Auth::user();
        return Usercpny::where('username', $user->username)
            ->where('status', 'A')
            ->pluck('cpny_id')
            ->toArray();
    }

    // VPACCESS grants HR-style users the ability to create Usage documents
    // outside their own company/department (see VplUsageController::store) —
    // it is not a "see everything" role, so only admin bypasses scoping here.
    private function isFullAccess(): bool
    {
        return Auth::user()->isPrimaryAdmin();
    }

    // Editing a product (form + save) is restricted to its creator; admin and
    // DIRECTORACCESS still bypass, same as everywhere else in this controller.
    private function canEditProduct(MsVplProduct $product): bool
    {
        if ($this->isFullAccess() || Auth::user()->hasFullDataScope()) {
            return true;
        }
        return $product->created_user === Auth::user()->username;
    }

    // Uploads a product photo to GCS under att-vpl/product-photo/ and
    // returns the object path (stored in ms_vpl_product.product_photo).
    private function uploadProductPhoto($file, string $product_id): string
    {
        $config  = config('filesystems.disks.gcs');
        $storage = new StorageClient([
            'projectId'   => $config['project_id'],
            'keyFilePath' => $config['key_file'],
        ]);
        $bucket = $storage->bucket($config['bucket']);

        $ext      = $file->getClientOriginalExtension();
        $filename = $product_id . '_' . time() . '.' . $ext;
        $gcsPath  = "att-vpl/product-photo/{$filename}";

        $bucket->upload(
            fopen($file->getPathname(), 'r'),
            [
                'name'          => $gcsPath,
                'predefinedAcl' => 'private',
                'metadata'      => ['contentType' => $file->getMimeType()],
            ]
        );

        return $gcsPath;
    }

    private function photoSignedUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        try {
            $config  = config('filesystems.disks.gcs');
            $storage = new StorageClient([
                'projectId'   => $config['project_id'],
                'keyFilePath' => $config['key_file'],
            ]);

            return $storage->bucket($config['bucket'])->object($path)->signedUrl(
                new \DateTimeImmutable('+10 minutes'),
                ['version' => 'v4']
            );
        } catch (\Throwable $e) {
            \Log::warning('VPL Product photo signed URL failed', ['path' => $path, 'error' => $e->getMessage()]);
            return null;
        }
    }

    // -------------------------------------------------------
    // INDEX
    // -------------------------------------------------------

    public function index()
    {
        return view('vpl.index');
    }

    // -------------------------------------------------------
    // MASTER PRODUCT
    // -------------------------------------------------------

    public function msproduct(Request $request)
    {
        $title     = 'Master Stock';
        $user      = Auth::user();
        $usercpny  = Usercpny::where('username', $user->username)->where('status', 'A')->get();
        $usercpny2 = Usercpny::where('username', $user->username)->where('status', 'A')->first();
        $category  = MsCategory::where('doctype', 'VPL')->where('status', 'A')->get();
        $cpnyIds   = $this->cpnyIds();

        // "All Product" — admin-only, system-wide view (ignores company scoping) with
        // its own Company/Type filters. The normal status cards stay scoped to the
        // user's own company, admin included.
        $isAdmin = $this->isFullAccess();

        // DIRECTORACCESS sees every product unscoped everywhere, on every tab —
        // distinct from the admin-only "All Product" tab, which stays admin-exclusive.
        $hasFullScope = $user->hasFullDataScope();

        $uomList = MsBaseUom::where('status', 'A')->orderBy('uomid')->pluck('uomid');

        $allCategories = MsCategory::where('doctype', 'VPL')
            ->where('categoryid', 'type')->where('groups', 'TYPE')->where('status', 'A')
            ->pluck('category_name');
        $allSources = MsCategory::where('doctype', 'VPL')
            ->where('categoryid', 'type')->where('groups', 'SOURCE')->where('status', 'A')
            ->pluck('category_name');
        $allCompanies = $isAdmin
            ? MsVplProduct::whereNotNull('cpnyid')->distinct()->orderBy('cpnyid')->pluck('cpnyid')
            : collect();

        // Status counts for filter cards — scoped to the user's own company, admin
        // included; DIRECTORACCESS holders count across everything instead.
        $base          = $hasFullScope ? MsVplProduct::query() : MsVplProduct::whereIn('cpnyid', $cpnyIds);
        $countAll      = (clone $base)->count();
        $countActive   = (clone $base)->where('status', 'A')->count();
        $countInactive = (clone $base)->where('status', 'X')->count();

        // "All Product" card — system-wide total, admin-only.
        $countAdminAll = $isAdmin ? MsVplProduct::count() : 0;

        if ($request->ajax()) {
            $statusFilter = $request->input('status_filter', '');
            $adminAll     = $isAdmin && $statusFilter === 'ADMINALL';

            $query = ($adminAll || $hasFullScope)
                ? MsVplProduct::query()
                : MsVplProduct::whereIn('cpnyid', $cpnyIds);

            // Status card filter
            if (in_array($statusFilter, ['A', 'X'], true)) {
                $query->where('status', $statusFilter);
            }

            if ($adminAll && $request->filled('filter_company')) {
                $query->where('cpnyid', $request->filter_company);
            }

            // Toolbar filters
            if ($request->filled('filter_type')) {
                $query->where('product_type', $request->filter_type);
            }
            if ($request->filled('filter_doc_id')) {
                $query->where('product_id', $request->filter_doc_id);
            }
            if ($request->filled('filter_category')) {
                $query->where('product_category', $request->filter_category);
            }
            if ($request->filled('filter_source')) {
                $query->where('product_source_type', $request->filter_source);
            }
            if ($request->filled('filter_product_name')) {
                $query->where('product_name', 'ilike', '%' . $request->filter_product_name . '%');
            }

            $rows = $query->get();

            // Total stock across all warehouses, plus a per-warehouse/expiry
            // breakdown for the hover tooltip — batched in one query rather
            // than per-row to avoid an N+1 on the product list.
            $stockByProduct = MsVplProductDetail::whereIn('product_id', $rows->pluck('product_id')->filter()->unique())
                ->where('status', 'A')
                ->where('qty_available', '>', 0)
                ->get(['product_id', 'whs_id', 'expired_date', 'qty_available'])
                ->groupBy('product_id');

            return Datatables::of($rows)
                ->addIndexColumn()
                ->addColumn('product_id', function ($row) {
                    $hash = Hashids::encode($row->id);
                    return '<button type="button" class="view-product-btn group/doc inline-flex min-w-[120px] items-center gap-2 whitespace-nowrap rounded-md border border-blue-100 bg-blue-50 px-3 py-1 text-[13px] font-semibold leading-6 tracking-wide tabular-nums text-blue-700 transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-95 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-600 dark:hover:text-white"
                        data-hash="' . $hash . '"><span class="text-xs opacity-80 transition-transform duration-300 group-hover/doc:scale-125 group-hover/doc:-rotate-6"><i class="fa-regular fa-file-lines"></i></span><span>'
                        . e($row->product_id) . '</span></button>';
                })
                ->addColumn('total_stock', function ($row) use ($stockByProduct) {
                    $details = $stockByProduct->get($row->product_id, collect());

                    $breakdown = $details
                        ->groupBy(fn ($d) => $d->whs_id . '|' . ($d->expired_date?->format('Y-m-d') ?? ''))
                        ->map(fn ($group) => [
                            'whs_id' => $group->first()->whs_id,
                            'exp'    => $group->first()->expired_date?->format('Y-m-d'),
                            'qty'    => (float) $group->sum('qty_available'),
                        ])
                        ->sortBy('whs_id')
                        ->values();

                    return [
                        'total'     => (float) $details->sum('qty_available'),
                        'breakdown' => $breakdown,
                    ];
                })
                ->addColumn('total_stock_sort', fn ($row) => (float) $stockByProduct
                    ->get($row->product_id, collect())
                    ->sum('qty_available'))
                ->addColumn('status', fn ($row) => $row->status === 'A'
                    ? '<span class="inline-flex min-w-[92px] items-center justify-center gap-2 whitespace-nowrap rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-200 dark:bg-green-500/10 dark:text-green-300 dark:ring-green-500/30"><span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>Active</span>'
                    : '<span class="inline-flex min-w-[92px] items-center justify-center gap-2 whitespace-nowrap rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-700 ring-1 ring-inset ring-red-200 dark:bg-red-500/10 dark:text-red-300 dark:ring-red-500/30"><span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>Inactive</span>')
                ->addColumn('action', function ($row) {
                    $active      = $row->status === 'A';
                    $toggleClass = $active ? 'deactivateProduct' : 'activateProduct';
                    $toggleLabel = $active ? 'Deactivate'        : 'Activate';
                    $toggleIcon  = $active ? 'fa-ban'            : 'fa-circle-check';
                    $toggleColor = $active
                        ? 'border-red-100 bg-red-50 text-red-600 hover:border-red-600 hover:bg-red-600'
                        : 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:border-emerald-600 hover:bg-emerald-600';
                    $button      = 'inline-flex h-8 items-center justify-center gap-1.5 whitespace-nowrap rounded-md border px-3 text-xs font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:text-white hover:shadow-md';

                    $edit = $this->canEditProduct($row)
                        ? '<button type="button" class="master-edit-btn ' . $button . ' border-amber-200 bg-amber-50 text-amber-800 hover:border-amber-500 hover:bg-amber-500" data-id="' . $row->id . '"><i class="fa-solid fa-pen-to-square"></i>Edit</button>'
                        : '';

                    $toggle = '<button type="button" class="master-toggle-btn ' . $button . ' min-w-[104px] ' . $toggleColor . '" data-id="' . $row->id . '" data-toggle="' . $toggleClass . '"><i class="fa-solid ' . $toggleIcon . '"></i>' . $toggleLabel . '</button>';

                    return '<div class="flex items-center justify-center gap-2">' . $edit . $toggle . '</div>';
                })
                ->rawColumns(['product_id', 'status', 'action'])
                ->make(true);
        }

        return view('pages.voucher_product.master', compact(
            'title', 'usercpny', 'usercpny2', 'category',
            'countAll', 'countActive', 'countInactive', 'countAdminAll', 'isAdmin',
            'allCategories', 'allSources', 'allCompanies', 'uomList'
        ));
    }

    public function getDocIds(Request $request)
    {
        abort_unless($request->ajax(), 404);
        $user    = Auth::user();
        $cpnyIds = $this->cpnyIds();

        $query = $this->isFullAccess()
            ? MsVplProduct::query()
            : MsVplProduct::whereIn('cpnyid', $cpnyIds);

        if ($request->filled('q')) {
            $query->where('product_id', 'ilike', '%' . $request->q . '%');
        }

        $results = $query->orderBy('product_id')->limit(50)->pluck('product_id')
            ->map(fn ($id) => ['id' => $id, 'text' => $id]);

        return response()->json(['results' => $results]);
    }

    public function export(Request $request)
    {
        $user     = Auth::user();
        $cpnyIds  = $this->cpnyIds();
        $adminAll = $this->isFullAccess() && $request->input('status_filter') === 'ADMINALL';

        $query = ($adminAll || $user->hasFullDataScope())
            ? MsVplProduct::query()
            : MsVplProduct::whereIn('cpnyid', $cpnyIds);

        if ($request->filled('status_filter') && in_array($request->status_filter, ['A', 'X'])) {
            $query->where('status', $request->status_filter);
        }
        if ($adminAll && $request->filled('filter_company')) {
            $query->where('cpnyid', $request->filter_company);
        }
        if ($request->filled('filter_type')) {
            $query->where('product_type', $request->filter_type);
        }
        if ($request->filled('filter_doc_id')) {
            $query->where('product_id', $request->filter_doc_id);
        }
        if ($request->filled('filter_category')) {
            $query->where('product_category', $request->filter_category);
        }
        if ($request->filled('filter_source')) {
            $query->where('product_source_type', $request->filter_source);
        }
        if ($request->filled('filter_product_name')) {
            $query->where('product_name', 'ilike', '%' . $request->filter_product_name . '%');
        }

        $rows = $query->orderBy('product_id')->get([
            'product_id', 'cpnyid', 'product_type', 'product_name', 'product_category',
            'product_source_company', 'product_source_tenant', 'product_source_type',
            'product_value', 'product_uom', 'status',
        ])->map(fn ($r) => [
            'Doc No'         => $r->product_id,
            'Company'        => $r->cpnyid,
            'Type'           => $r->product_type === 'V' ? 'Voucher' : ($r->product_type === 'P' ? 'Product' : $r->product_type),
            'Product Name'   => $r->product_name,
            'Category'       => $r->product_category,
            'Source PT'      => $r->product_source_company,
            'Tenant / Event' => $r->product_source_tenant,
            'Source Type'    => $r->product_source_type,
            'Value'          => $r->product_value,
            'UOM'            => $r->product_uom,
            'Status'         => $r->status === 'A' ? 'Active' : 'Inactive',
        ]);

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ArrayExport($rows),
            'master-stock-' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function edit_product(Request $request, $id)
    {
        abort_unless($request->ajax(), 404);
        $msproduct = MsVplProduct::findOrFail($id);

        if (!$this->isFullAccess() && !Auth::user()->hasFullDataScope() && !in_array($msproduct->cpnyid, $this->cpnyIds(), true)) {
            abort(403);
        }

        if (!$this->canEditProduct($msproduct)) {
            abort(403);
        }

        $hasStock = MsVplProductDetail::where('product_id', $msproduct->product_id)
            ->where('status', 'A')
            ->where('qty_available', '>', 0)
            ->exists();

        return response()->json([
            'msproduct' => $msproduct,
            'has_stock' => $hasStock,
            'photo_url' => $this->photoSignedUrl($msproduct->product_photo),
        ]);
    }

    public function save_product(Request $request)
    {
        $request->validate([
            'cpnyid'        => 'required|string',
            'product_name'  => 'required|string',
            'product_type'  => 'required|in:V,P',
            'product_photo' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
        ]);

        $user     = Auth::user();
        $username = $user->username ?? 'system';
        $key_id   = $request->key_id;

        $msproduct     = $key_id ? MsVplProduct::findOrFail($key_id) : null;
        $existingPhoto = $msproduct->product_photo ?? null;

        if ($request->product_type === 'P' && !$request->hasFile('product_photo') && empty($existingPhoto)) {
            return response()->json([
                'message' => 'Product photo is required for Product type.',
            ], 422);
        }

        if ($msproduct && !$this->canEditProduct($msproduct)) {
            return response()->json([
                'message' => 'You are not authorized to edit this product.',
            ], 403);
        }

        try {
            if ($msproduct) {
                $msproduct->cpnyid                 = $request->cpnyid;
                $msproduct->product_name           = $request->product_name;
                $msproduct->product_type           = $request->product_type;
                $msproduct->product_category       = $request->product_category;
                $msproduct->product_source_type    = $request->product_source_type;
                $msproduct->product_source_company = strtoupper($request->product_source_company ?? '');
                $msproduct->product_source_tenant  = strtoupper($request->product_source_tenant ?? '');
                $msproduct->product_remark         = $request->product_remark;
                $msproduct->product_value          = $request->product_value;
                $msproduct->product_uom            = strtoupper($request->product_uom ?? '');
                $msproduct->product_check_exp      = $request->product_check_exp;
                $msproduct->updated_user           = $username;

                if ($request->hasFile('product_photo')) {
                    $msproduct->product_photo = $this->uploadProductPhoto($request->file('product_photo'), $msproduct->product_id);
                }

                $msproduct->save();
            } else {
                // Next number per (type + company) is derived from the highest
                // existing product_id with this prefix, not from ms_autonbr —
                // this keeps it correct even after manual/bulk data imports.
                // Generates: V100001 (AW), V200001 (EP), P300001 (PSA), etc.
                $cpnyPrefix = self::COMPANY_PREFIX[$request->cpnyid] ?? '0';
                $prefix     = $request->product_type . $cpnyPrefix;

                // ms_vpl_product.product_id has a unique index; if two users
                // save at the same moment the loser hits it and retries with
                // the next number.
                $msproduct = null;
                for ($attempt = 1; $attempt <= 5; $attempt++) {
                    $maxSuffix = MsVplProduct::where('product_id', 'like', $prefix.'%')
                        ->get(['product_id'])
                        ->map(fn ($row) => (int) substr($row->product_id, strlen($prefix)))
                        ->max();

                    $product_id = $prefix . sprintf('%05d', ($maxSuffix ?? 0) + 1);

                    try {
                        $msproduct = MsVplProduct::create([
                            'product_id'            => $product_id,
                            'cpnyid'                => $request->cpnyid,
                            'product_name'          => $request->product_name,
                            'product_type'          => $request->product_type,
                            'product_category'      => $request->product_category,
                            'product_source_type'   => $request->product_source_type,
                            'product_source_company'=> strtoupper($request->product_source_company ?? ''),
                            'product_source_tenant' => strtoupper($request->product_source_tenant ?? ''),
                            'product_remark'        => $request->product_remark,
                            'product_value'         => $request->product_value,
                            'product_uom'           => strtoupper($request->product_uom ?? ''),
                            'product_check_exp'     => $request->product_check_exp,
                            'product_photo'         => null,
                            'status'                => 'A',
                            'created_user'          => $username,
                        ]);
                        break;
                    } catch (\Illuminate\Database\QueryException $e) {
                        if ($e->getCode() !== '23505' || $attempt === 5) {
                            throw $e;
                        }
                    }
                }

                if ($request->hasFile('product_photo')) {
                    $msproduct->product_photo = $this->uploadProductPhoto($request->file('product_photo'), $msproduct->product_id);
                    $msproduct->save();
                }
            }

            return response()->json([
                'success' => 'Product saved successfully.',
                'eid'     => Hashids::encode($msproduct->id),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to save product',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    // -------------------------------------------------------
    // DEACTIVATE / ACTIVATE
    // -------------------------------------------------------

    public function deactivate(Request $request, $id)
    {
        abort_unless($request->ajax(), 404);
        $msproduct = MsVplProduct::findOrFail($id);

        // Block if any qty_available > 0 exists across all detail rows
        $totalQty = MsVplProductDetail::where('product_id', $msproduct->product_id)
            ->where('status', 'A')
            ->sum('qty_available');

        if ($totalQty > 0) {
            return response()->json([
                'message' => "Cannot deactivate. This product still has {$totalQty} qty in stock.",
            ], 422);
        }

        $msproduct->status       = 'X';
        $msproduct->updated_user = Auth::user()->username;
        $msproduct->save();

        return response()->json(['success' => 'Product deactivated.']);
    }

    public function activate(Request $request, $id)
    {
        abort_unless($request->ajax(), 404);
        $msproduct = MsVplProduct::findOrFail($id);

        $msproduct->status       = 'A';
        $msproduct->updated_user = Auth::user()->username;
        $msproduct->save();

        return response()->json(['success' => 'Product activated.']);
    }

    // -------------------------------------------------------
    // VIEW PRODUCT DETAIL
    // -------------------------------------------------------

    public function viewproductJson(Request $request, $hash)
    {
        abort_unless($request->ajax(), 404);
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $msproduct = MsVplProduct::findOrFail($id);

        if (!$this->isFullAccess() && !Auth::user()->hasFullDataScope() && !in_array($msproduct->cpnyid, $this->cpnyIds(), true)) {
            abort(403);
        }

        $stock = MsVplProductDetail::where('product_id', $msproduct->product_id)
            ->where('status', 'A')
            ->orderBy('expired_date')
            ->get(['expired_date', 'whs_id', 'qty_available']);

        $attachments = TrAttachment::where('refnbr', $msproduct->product_id)
            ->where('doctype', 'VPLPROD')
            ->where('status', 'A')
            ->orderByDesc('created_at')
            ->get(['attachment_name', 'created_by', 'created_at', 'extention']);

        $relatedTransactions = $this->relatedOnProgressTransactions($msproduct->product_id);

        return response()->json([
            'product'              => $msproduct,
            'stock'                => $stock,
            'attachments'          => $attachments,
            'related_transactions' => $relatedTransactions,
            'photo_url'            => $this->photoSignedUrl($msproduct->product_photo),
            'can_edit'             => $this->canEditProduct($msproduct),
        ]);
    }

    // Transfer/Return Transfer/Usage/Return Usage documents still "On Progress"
    // (header status 'P', i.e. not yet Completed/Rejected/Cancelled) that touch
    // this product — so a user looking at Master Stock can see stock is already
    // spoken for by a document in flight, not just what's currently on hand.
    private function relatedOnProgressTransactions(string $productId)
    {
        $transfers = TrxVplTransferDetail::query()
            ->join('tr_vpl_transfer as h', 'h.transfer_id', '=', 'tr_vpl_transfer_detail.transfer_id')
            ->where('tr_vpl_transfer_detail.product_id', $productId)
            ->where('h.status', 'P')
            ->orderByDesc('h.transfer_date')
            ->get([
                'h.id as header_id',
                'h.transfer_id',
                'h.transfer_date',
                'h.transfertype',
                'tr_vpl_transfer_detail.from_whs_id',
                'tr_vpl_transfer_detail.to_whs_id',
                'tr_vpl_transfer_detail.qty_transfer',
            ])
            ->map(fn ($r) => [
                'type'   => $r->transfertype === 'ReturnTf' ? 'Return Transfer' : 'Transfer',
                'doc_no' => $r->transfer_id,
                'date'   => $r->transfer_date,
                'whs'    => trim(($r->from_whs_id ?? '-') . ' → ' . ($r->to_whs_id ?? '-')),
                'qty'    => (float) $r->qty_transfer,
                'url'    => route('transfervp.show', $r->header_id),
            ]);

        $usages = TrxVplUsageDetail::query()
            ->join('tr_vpl_usage as h', 'h.usage_id', '=', 'tr_vpl_usage_detail.usage_id')
            ->where('tr_vpl_usage_detail.product_id', $productId)
            ->where('h.status', 'P')
            ->orderByDesc('h.usage_date')
            ->get([
                'h.id as header_id',
                'h.usage_id',
                'h.usage_date',
                'h.usagetype',
                'tr_vpl_usage_detail.whs_id',
                'tr_vpl_usage_detail.qty_usage',
                'tr_vpl_usage_detail.qty_return_usage',
            ])
            ->map(fn ($r) => [
                'type'   => $r->usagetype === 'Return' ? 'Return Usage' : 'Usage',
                'doc_no' => $r->usage_id,
                'date'   => $r->usage_date,
                'whs'    => $r->whs_id ?? '-',
                'qty'    => (float) ($r->usagetype === 'Return' ? $r->qty_return_usage : $r->qty_usage),
                'url'    => route('usagevp.show', $r->header_id),
            ]);

        return $transfers->concat($usages)->sortByDesc('date')->values();
    }

    public function viewproduct($hash)
    {
        // No standalone page for this anymore — the product detail is shown in a
        // modal on the master list, which deep-links as /msproduct/{hash} (no
        // "/view" suffix) and auto-opens via init.js. Redirect old-style
        // "/view" links there instead of rendering a page that no longer exists.
        return redirect()->route('vpl.msproduct.view_by_hash', $hash);
    }

    // -------------------------------------------------------
    // PRODUCT DETAIL LINES
    // -------------------------------------------------------

    public function saveProductDetail(Request $request)
    {
        abort_unless($request->ajax(), 404);
        $product_id = $request->input('product_id');
        $username   = Auth::user()->username;
        $cpnyid     = MsVplProduct::where('product_id', $product_id)->value('cpnyid');
        $now        = now();

        DB::connection('pgsql5')->beginTransaction();
        try {
            foreach ($request->addmore as $value) {
                MsVplProductDetail::create([
                    'product_id'    => $product_id,
                    'qty_available' => $value['qty'],
                    'expired_date'  => $value['expired_date'],
                    'whs_id'        => $value['source_whs'],
                    'status'        => 'A',
                    'created_user'  => $username,
                    'created_at'    => now(),
                ]);

                // Master Stock has no Receive document behind it, so it never
                // goes through sp_process_vpl — post it straight to the balance
                // table ourselves, otherwise the Stock Voucher report (which
                // reads ms_vpl_product_bal, not ms_vpl_product_detail) never
                // sees this qty at all.
                $this->postStockBalanceIn($product_id, $cpnyid, $value['expired_date'], $value['source_whs'], (float) $value['qty'], $now, $username);
            }
            DB::connection('pgsql5')->commit();
            return response()->json(['success' => 'Product details added successfully.']);
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();
            return response()->json([
                'message' => 'Failed to save product details',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    private function postStockBalanceIn(string $productId, ?string $cpnyid, string $expiredDate, string $whsId, float $qty, \Illuminate\Support\Carbon $postDate, string $username): void
    {
        $year = $postDate->year;
        $mm   = str_pad((string) $postDate->month, 2, '0', STR_PAD_LEFT);
        $col  = "period{$mm}in";

        $bal = MsVplProductBal::where('year', $year)
            ->where('product_id', $productId)
            ->where('expired_date', $expiredDate)
            ->where('whs_id', $whsId)
            ->first();

        if ($bal) {
            $bal->{$col}      = ($bal->{$col} ?? 0) + $qty;
            $bal->updated_user = $username;
            $bal->save();
            return;
        }

        MsVplProductBal::create([
            'year'         => $year,
            'perpost'      => $year.$mm,
            'product_id'   => $productId,
            'expired_date' => $expiredDate,
            'cpnyid'       => $cpnyid,
            'whs_id'       => $whsId,
            'begqty'       => 0,
            $col           => $qty,
            'status'       => 'A',
            'created_user' => $username,
        ]);
    }

    // -------------------------------------------------------
    // ATTACHMENT (GCS via TrAttachmentController)
    // -------------------------------------------------------

    public function saveProductAttach(Request $request)
    {
        abort_unless($request->ajax(), 404);
        if (!$request->hasFile('attachment')) {
            return response()->json(['message' => 'No file uploaded'], 422);
        }

        $product_id = $request->input('product_id');
        $username   = Auth::user()->username;

        $meta = [
            'refnbr'      => $product_id,
            'doctype'     => 'VPLPROD',
            'cpnyid'      => $request->cpnyid ?? '',
            'base_folder' => 'att-vpl-product',
            'created_by'  => $username,
        ];

        try {
            app(TrAttachmentController::class)->uploadInternal($meta, (array) $request->file('attachment'));
            return response()->json(['success' => 'Attachment added successfully.']);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Upload failed',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    // -------------------------------------------------------
    // CATEGORY & SOURCE (AJAX dropdowns)
    // Both live in ms_category, split by groups column:
    //   groups = 'TYPE'   → product category dropdown
    //   groups = 'SOURCE' → product source dropdown
    // -------------------------------------------------------

    public function getCategoryproduct(Request $request)
    {
        abort_unless($request->ajax(), 404);
        $categories = MsCategory::where('doctype', 'VPL')
            ->where('categoryid', 'type')
            ->where('groups', 'TYPE')
            ->where('status', 'A')
            ->get();
        return response()->json($categories);
    }

    public function getSourceproduct(Request $request)
    {
        abort_unless($request->ajax(), 404);
        $sources = MsCategory::where('doctype', 'VPL')
            ->where('categoryid', 'type')
            ->where('groups', 'SOURCE')
            ->where('status', 'A')
            ->get();
        return response()->json($sources);
    }

    // -------------------------------------------------------
    // PRODUCT TARGET DATE
    // -------------------------------------------------------

    public function producttarget(Request $request)
    {
        $title     = 'Master Stock Target Date';
        $user      = Auth::user();
        $usercpny  = Usercpny::where('username', $user->username)->get();
        $usercpny2 = Usercpny::where('username', $user->username)->first();
        $cpnyIds   = $this->cpnyIds();

        if ($request->ajax()) {
            $data = $this->isFullAccess()
                ? MsVplProductTargetDate::all()
                : MsVplProductTargetDate::whereIn('cpnyid', $cpnyIds)->get();

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('status', fn ($row) => $row->status === 'A'
                    ? '<a href="javascript:void(0)" class="label label-success">Active</a>'
                    : '<a href="javascript:void(0)" class="label label-danger">In Active</a>')
                ->addColumn('action', fn ($row) =>
                    '<a href="javascript:void(0)" data-id="' . $row->product_id . '" class="btn btn-sm btn-primary detailBtn">Detail</a>')
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('vpl.msproduct.producttarget', compact('title', 'usercpny', 'usercpny2'));
    }

    public function getProductDetails(Request $request, $product_id)
    {
        abort_unless($request->ajax(), 404);
        $details = MsVplProductDetail::where('product_id', $product_id)
            ->get(['product_id', 'expired_date', 'target_date', 'cpnyid', 'whs_id', 'qty_available']);
        return response()->json($details);
    }

    public function updateTargetDate(Request $request)
    {
        abort_unless($request->ajax(), 404);
        $request->validate(['target_date' => 'required|date']);
        MsVplProductDetail::where('product_id', $request->product_id)
            ->update(['target_date' => $request->target_date]);
        return response()->json(['message' => 'Target date updated']);
    }

    // -------------------------------------------------------
    // SETUP AGING
    // -------------------------------------------------------

    public function setupaging(Request $request)
    {
        $title     = 'Setup Aging';
        $user      = Auth::user();
        $usercpny  = Usercpny::where('username', $user->username)->get();
        $usercpny2 = Usercpny::where('username', $user->username)->first();

        if ($request->ajax()) {
            return Datatables::of(MsVplAging::all())
                ->addIndexColumn()
                ->addColumn('status', fn ($row) => $row->status === 'A'
                    ? '<a href="javascript:void(0)" class="label label-success">Active</a>'
                    : '<a href="javascript:void(0)" class="label label-danger">In Active</a>')
                ->addColumn('action', fn ($row) =>
                    '<a href="javascript:void(0)" data-id="' . $row->id . '" class="btn btn-sm editAging" style="background-color:#FFCD05;color:white">Edit</a>')
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('vpl.msproduct.setupaging', compact('title', 'usercpny', 'usercpny2'));
    }

    public function edit_aging(Request $request, int $id)
    {
        abort_unless($request->ajax(), 404);
        $setupaging = MsVplAging::findOrFail($id);
        return response()->json(['setupaging' => $setupaging]);
    }

    public function save_aging(Request $request)
    {
        $username = Auth::user()->username;
        $key_id   = $request->key_id;

        try {
            if ($key_id) {
                $msaging               = MsVplAging::findOrFail($key_id);
                $msaging->age_descr    = $request->age_descr;
                $msaging->start_age    = $request->start_age;
                $msaging->end_age      = $request->end_age;
                $msaging->order_age    = $request->order_age;
                $msaging->status       = $request->status;
                $msaging->updated_user = $username;
                $msaging->save();
            } else {
                MsVplAging::create([
                    'age_descr'    => $request->age_descr,
                    'start_age'    => $request->start_age,
                    'end_age'      => $request->end_age,
                    'order_age'    => $request->order_age,
                    'status'       => 'A',
                    'created_user' => $username,
                ]);
            }
            return response()->json(['success' => 'Aging saved successfully.']);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to save aging',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
