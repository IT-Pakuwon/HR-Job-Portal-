<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Vinkla\Hashids\Facades\Hashids;
use Google\Cloud\Storage\StorageClient;
use App\Models\MsVplProduct;
use App\Models\MsVplProductDetail;
use App\Models\MsVplProductTargetDate;
use App\Models\MsVplWarehouse;
use App\Models\MsVplAging;
use App\Models\MsCategory;
use App\Models\MsBaseUom;
use App\Models\MsDepartment;
use App\Models\TrAttachment;
use App\Models\Usercpny;
use App\Http\Controllers\TrAttachmentController;
use App\Http\Controllers\Traits\HasAutonbr;
use DataTables;

class VplMsProductController extends Controller
{
    use HasAutonbr;

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

        $uomList = MsBaseUom::where('status', 'A')->orderBy('uomid')->pluck('uomid');

        $allCategories = MsCategory::where('doctype', 'VPL')
            ->where('categoryid', 'type')->where('groups', 'TYPE')->where('status', 'A')
            ->pluck('category_name');
        $allSources = MsCategory::where('doctype', 'VPL')
            ->where('categoryid', 'type')->where('groups', 'SOURCE')->where('status', 'A')
            ->pluck('category_name');

        // Status counts for filter cards
        $base     = $user->role === 'admin'
            ? MsVplProduct::query()
            : MsVplProduct::whereIn('cpnyid', $cpnyIds);
        $countAll      = (clone $base)->count();
        $countActive   = (clone $base)->where('status', 'A')->count();
        $countInactive = (clone $base)->where('status', 'X')->count();

        if ($request->ajax()) {
            $query = $user->role === 'admin'
                ? MsVplProduct::query()
                : MsVplProduct::whereIn('cpnyid', $cpnyIds);

            // Status card filter
            $statusFilter = $request->input('status_filter', '');
            if (in_array($statusFilter, ['A', 'X'], true)) {
                $query->where('status', $statusFilter);
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

            return Datatables::of($query->get())
                ->addIndexColumn()
                ->addColumn('product_id', function ($row) {
                    $hash = Hashids::encode($row->id);
                    return '<button class="view-product-btn inline-flex items-center justify-center rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white transition-colors hover:bg-slate-700 dark:bg-blue-600 dark:hover:bg-blue-500"
                        data-hash="' . $hash . '">'
                        . $row->product_id . '</button>';
                })
                ->addColumn('status', fn ($row) => $row->status === 'A'
                    ? '<span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-800">Active</span>'
                    : '<span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-800">Inactive</span>')
                ->addColumn('action', function ($row) {
                    $active      = $row->status === 'A';
                    $toggleClass = $active ? 'deactivateProduct' : 'activateProduct';
                    $toggleLabel = $active ? 'Deactivate'        : 'Activate';
                    $toggleIcon  = $active ? 'fa-ban text-red-500' : 'fa-circle-check text-green-600';

                    return '
                        <button class="action-btn inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 shadow-sm hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:text-slate-300"
                            data-id="'     . $row->id      . '"
                            data-active="' . ($active ? '1' : '0') . '"
                            data-toggle="' . $toggleClass   . '"
                            data-label="'  . $toggleLabel   . '"
                            data-icon="'   . $toggleIcon    . '">
                            Actions <i class="fa-solid fa-chevron-down text-[10px]"></i>
                        </button>';
                })
                ->rawColumns(['product_id', 'status', 'action'])
                ->make(true);
        }

        return view('pages.voucher_product.master', compact(
            'title', 'usercpny', 'usercpny2', 'category',
            'countAll', 'countActive', 'countInactive',
            'allCategories', 'allSources', 'uomList'
        ));
    }

    public function getDocIds(Request $request)
    {
        abort_unless($request->ajax(), 404);
        $user    = Auth::user();
        $cpnyIds = $this->cpnyIds();

        $query = $user->role === 'admin'
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
        $user    = Auth::user();
        $cpnyIds = $this->cpnyIds();

        $query = $user->role === 'admin'
            ? MsVplProduct::query()
            : MsVplProduct::whereIn('cpnyid', $cpnyIds);

        if ($request->filled('status_filter') && in_array($request->status_filter, ['A', 'X'])) {
            $query->where('status', $request->status_filter);
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

        $hasStock = MsVplProductDetail::where('product_id', $msproduct->product_id)
            ->where('status', 'A')
            ->where('qty_available', '>', 0)
            ->exists();

        return response()->json(['msproduct' => $msproduct, 'has_stock' => $hasStock]);
    }

    public function save_product(Request $request)
    {
        $request->validate([
            'cpnyid'       => 'required|string',
            'product_name' => 'required|string',
            'product_type' => 'required|in:V,P',
        ]);

        $user     = Auth::user();
        $username = $user->username ?? 'system';
        $key_id   = $request->key_id;

        try {
            if ($key_id) {
                $msproduct = MsVplProduct::findOrFail($key_id);
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
                $msproduct->status                 = $request->status;
                $msproduct->updated_user           = $username;
                $msproduct->save();
            } else {
                // Counter per (type + company), never resets
                // Generates: V100001 (AW), V200001 (EP), P300001 (PSA), etc.
                $cpnyPrefix = self::COMPANY_PREFIX[$request->cpnyid] ?? '0';
                $auto       = $this->nextAutonbr($request->product_type, 0, $request->cpnyid, $username, 'VPL Product');
                $product_id = $request->product_type . $cpnyPrefix . sprintf('%05d', $auto['next']);

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
                    'status'                => 'A',
                    'created_user'          => $username,
                ]);
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

        $stock = MsVplProductDetail::where('product_id', $msproduct->product_id)
            ->where('status', 'A')
            ->orderBy('expired_date')
            ->get(['expired_date', 'whs_id', 'qty_available']);

        $attachments = TrAttachment::where('refnbr', $msproduct->product_id)
            ->where('doctype', 'VPLPROD')
            ->where('status', 'A')
            ->orderByDesc('created_at')
            ->get(['attachment_name', 'created_by', 'created_at', 'extention']);

        return response()->json([
            'product'     => $msproduct,
            'stock'       => $stock,
            'attachments' => $attachments,
        ]);
    }

    public function viewproduct($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $msproduct = MsVplProduct::findOrFail($id);

        $msproductdetail = MsVplProductDetail::where('product_id', $msproduct->product_id)
            ->where('status', 'A')
            ->orderBy('cpnyid')
            ->orderBy('expired_date')
            ->orderBy('whs_id')
            ->get();

        $mswhs = MsVplWarehouse::all();

        // Load attachments from GCS
        $rows = TrAttachment::where('refnbr', $msproduct->product_id)
            ->where('doctype', 'VPLPROD')
            ->where('status', 'A')
            ->orderByDesc('created_at')
            ->get();

        $attachments = collect();
        if ($rows->count()) {
            $config      = config('filesystems.disks.gcs');
            $keyFilePath = $config['key_file'];
            if (!Str::startsWith($keyFilePath, ['/', 'C:\\', 'D:\\'])) {
                $keyFilePath = base_path($keyFilePath);
            }
            $storage = new StorageClient(['projectId' => $config['project_id'], 'keyFilePath' => $keyFilePath]);
            $bucket  = $storage->bucket($config['bucket']);

            $attachments = $rows->map(function ($r) use ($bucket) {
                $objectPath = rtrim($r->folder, '/') . '/' . $r->filename;
                $signedUrl  = null;
                try {
                    $signedUrl = $bucket->object($objectPath)->signedUrl(
                        new \DateTimeImmutable('+10 minutes'),
                        ['version' => 'v4']
                    );
                } catch (\Throwable $e) {
                    \Log::warning('VPL Product signed URL failed', ['path' => $objectPath, 'error' => $e->getMessage()]);
                }
                return (object) [
                    'id'           => $r->id,
                    'display_name' => $r->attachment_name,
                    'created_by'   => $r->created_by,
                    'created_at'   => $r->created_at,
                    'url'          => $signedUrl,
                    'extention'    => $r->extention,
                    'size'         => $r->filesize,
                ];
            });
        }

        return view('vpl.msproduct.viewproduct', compact('msproduct', 'msproductdetail', 'mswhs', 'attachments', 'hash'));
    }

    // -------------------------------------------------------
    // PRODUCT DETAIL LINES
    // -------------------------------------------------------

    public function saveProductDetail(Request $request)
    {
        abort_unless($request->ajax(), 404);
        $product_id = $request->input('product_id');
        $username   = Auth::user()->username;

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
            $data = $user->role === 'admin'
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
