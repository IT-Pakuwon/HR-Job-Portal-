<?php

namespace App\Http\Controllers;

use App\Models\TrFinding;
use App\Models\MsCategory;
use App\Models\MsFindingItem;
use App\Models\MsFindingSubItem;
use App\Models\MsLocation;
use App\Models\MsSubLocation;
use App\Models\TrAttachment;
use App\Models\Usercpny;
use App\Models\TrFindingActivity;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class FindingController extends Controller
{
    public function index()
    {
        $username = Auth::user()->username;
        $companies = $this->companies($username);
        $baseQuery = TrFinding::query()->whereIn('cpny_id', $companies);

        $allFinding = (clone $baseQuery)->count();
        $myFinding = (clone $baseQuery)->where('created_by', $username)->count();
        $openFinding = (clone $baseQuery)->where(fn (Builder $query) => $this->open($query))->count();
        $closeFinding = (clone $baseQuery)->where(fn (Builder $query) => $this->closed($query))->count();
        $departments = (clone $baseQuery)->whereNotNull('department_id')
            ->distinct()->orderBy('department_id')->pluck('department_id');
        $locationIds = (clone $baseQuery)->whereNotNull('location_id')
            ->distinct()->pluck('location_id');
        $locations = MsLocation::query()
            ->whereIn('cpny_id', $companies)
            ->whereIn('location_id', $locationIds)
            ->orderBy('location_name')
            ->get(['location_id', 'location_name'])
            ->unique('location_id')
            ->values();
        $categoryIds = (clone $baseQuery)->whereNotNull('finding_category')
            ->distinct()->pluck('finding_category');
        $categories = MsCategory::query()
            ->where('doctype', 'OPS')
            ->whereIn('categoryid', $categoryIds)
            ->orderBy('category_name')
            ->get(['categoryid', 'category_name']);
        $itemIds = (clone $baseQuery)->whereNotNull('finding_item')
            ->distinct()->pluck('finding_item');
        $findingItems = MsFindingItem::query()
            ->whereIn('finding_item', $itemIds)
            ->orderBy('finding_name')
            ->get(['finding_item', 'finding_name']);

        return view('pages.finding.finding', compact(
            'companies',
            'allFinding',
            'myFinding',
            'openFinding',
            'closeFinding',
            'departments',
            'locations',
            'categories',
            'findingItems'
        ));
    }

    public function json(Request $request)
    {
        $username = Auth::user()->username;
        $companies = $this->companies($username);
        $companyId = trim((string) $request->input('cpny_id', ''));
        $filter = strtolower((string) $request->input('filter', 'my'));
        $draw = (int) $request->input('draw', 1);
        $start = max((int) $request->input('start', 0), 0);
        $length = min(max((int) $request->input('length', 25), 1), 100);
        $search = trim((string) $request->input('search.value', ''));
        $departmentId = trim((string) $request->input('department_id', ''));
        $locationId = trim((string) $request->input('location_id', ''));
        $categoryId = trim((string) $request->input('finding_category', ''));
        $itemId = trim((string) $request->input('finding_item', ''));
        $statusFilter = strtolower(trim((string) $request->input('status', '')));

        $query = TrFinding::query()->whereIn('cpny_id', $companies);

        if ($companyId !== '') {
            abort_unless($companies->contains($companyId), 403);
            $query->where('cpny_id', $companyId);
        }

        if ($filter === 'my') {
            $query->where('created_by', $username);
        } elseif ($filter === 'open') {
            $query->where(fn (Builder $subQuery) => $this->open($subQuery));
        } elseif (in_array($filter, ['close', 'closed'], true)) {
            $query->where(fn (Builder $subQuery) => $this->closed($subQuery));
        }

        if ($departmentId !== '') {
            $query->where('department_id', $departmentId);
        }
        if ($locationId !== '') {
            $query->where('location_id', $locationId);
        }
        if ($categoryId !== '') {
            $query->where('finding_category', $categoryId);
        }
        if ($itemId !== '') {
            $query->where('finding_item', $itemId);
        }
        if ($statusFilter === 'open') {
            $query->where(fn (Builder $subQuery) => $this->open($subQuery));
        } elseif (in_array($statusFilter, ['close', 'closed'], true)) {
            $query->where(fn (Builder $subQuery) => $this->closed($subQuery));
        }

        $recordsTotal = (clone $query)->count();

        if ($search !== '') {
            $query->where(function (Builder $subQuery) use ($search) {
                $subQuery->where('finding_id', 'ilike', "%{$search}%")
                    ->orWhere('cpny_id', 'ilike', "%{$search}%")
                    ->orWhere('department_id', 'ilike', "%{$search}%")
                    ->orWhere('location_id', 'ilike', "%{$search}%")
                    ->orWhere('finding_category', 'ilike', "%{$search}%")
                    ->orWhere('finding_item', 'ilike', "%{$search}%")
                    ->orWhere('finding_subitem', 'ilike', "%{$search}%")
                    ->orWhere('issue', 'ilike', "%{$search}%")
                    ->orWhere('solution', 'ilike', "%{$search}%")
                    ->orWhere('created_by', 'ilike', "%{$search}%");
            });
        }

        $recordsFiltered = (clone $query)->count();
        $columns = [
            0 => 'finding_id',
            1 => 'finding_date',
            2 => 'cpny_id',
            3 => 'department_id',
            4 => 'location_id',
            5 => 'finding_category',
            6 => 'finding_item',
            7 => 'issue',
            8 => 'status',
            9 => 'created_by',
        ];
        $orderColumn = $columns[(int) $request->input('order.0.column', 1)] ?? 'finding_date';
        $orderDirection = strtolower((string) $request->input('order.0.dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $data = $query->orderBy($orderColumn, $orderDirection)
            ->orderByDesc('id')
            ->skip($start)
            ->take($length)
            ->get([
                'id', 'finding_id', 'finding_date', 'cpny_id', 'department_id',
                'location_id', 'sub_location_id', 'finding_category', 'finding_item',
                'finding_subitem', 'issue', 'solution', 'user_solution', 'status',
                'created_by', 'completed_by', 'completed_at',
            ]);

        $locations = collect();
        if ($data->isNotEmpty()) {
            $locations = MsLocation::query()
                ->where(function (Builder $locationQuery) use ($data) {
                    foreach ($data->unique(fn ($row) => "{$row->cpny_id}|{$row->location_id}") as $row) {
                        $locationQuery->orWhere(fn (Builder $pair) => $pair
                            ->where('cpny_id', $row->cpny_id)
                            ->where('location_id', $row->location_id));
                    }
                })
                ->get(['cpny_id', 'location_id', 'location_name'])
                ->keyBy(fn ($location) => "{$location->cpny_id}|{$location->location_id}");
        }
        $categories = MsCategory::query()
            ->where('doctype', 'OPS')
            ->whereIn('categoryid', $data->pluck('finding_category')->filter()->unique())
            ->pluck('category_name', 'categoryid');
        $items = MsFindingItem::query()
            ->whereIn('finding_item', $data->pluck('finding_item')->filter()->unique())
            ->pluck('finding_name', 'finding_item');
        $commentCounts = TrFindingActivity::query()
            ->whereIn('finding_id', $data->pluck('finding_id'))
            ->where('status_activity', 'COMMENT')
            ->where(fn (Builder $activityQuery) => $activityQuery
                ->whereNull('status')
                ->orWhere('status', '<>', 'X'))
            ->selectRaw('finding_id, COUNT(*) AS total')
            ->groupBy('finding_id')
            ->pluck('total', 'finding_id');

        $data->transform(function (TrFinding $finding) use ($username, $locations, $categories, $items, $commentCounts) {
                $finding->finding_date_label = $finding->finding_date?->format('d M Y');
                $finding->location_name = $locations->get("{$finding->cpny_id}|{$finding->location_id}")?->location_name
                    ?? $finding->location_id;
                $finding->category_name = $categories->get($finding->finding_category)
                    ?? $finding->finding_category;
                $finding->item_name = $items->get($finding->finding_item)
                    ?? $finding->finding_item;
                $finding->status_label = $finding->completed_at || strtoupper((string) $finding->status) === 'C'
                    ? 'Close'
                    : 'Open';
                $finding->is_mine = $finding->created_by === $username;
                $finding->comments_count = (int) ($commentCounts->get($finding->finding_id) ?? 0);

                return $finding;
            });

        return response()->json(compact('draw', 'recordsTotal', 'recordsFiltered', 'data'));
    }

    public function show(string $findingId)
    {
        $finding = TrFinding::query()
            ->where('finding_id', $findingId)
            ->whereIn('cpny_id', $this->companies(Auth::user()->username))
            ->firstOrFail();

        $locationName = MsLocation::query()
            ->where('cpny_id', $finding->cpny_id)
            ->where('location_id', $finding->location_id)
            ->value('location_name');
        $categoryName = MsCategory::query()
            ->where('doctype', 'OPS')
            ->where('categoryid', $finding->finding_category)
            ->value('category_name');
        $itemName = MsFindingItem::query()
            ->where('finding_item', $finding->finding_item)
            ->value('finding_name');
        $subLocationName = MsSubLocation::query()
            ->where('cpny_id', $finding->cpny_id)
            ->where('location_id', $finding->location_id)
            ->where('sub_location_id', $finding->sub_location_id)
            ->value('sub_location_name');
        $subItemName = MsFindingSubItem::query()
            ->where('finding_subitem', $finding->finding_subitem)
            ->value('finding_subitem_name');

        $findingData = $finding->toArray();
        $findingData['finding_date_label'] = $finding->finding_date?->format('d M Y');
        $findingData['completed_at_label'] = $finding->completed_at?->format('d M Y H:i');
        $findingData['location_name'] = $locationName ?: $finding->location_id;
        $findingData['sub_location_name'] = $subLocationName ?: $finding->sub_location_id;
        $findingData['category_name'] = $categoryName ?: $finding->finding_category;
        $findingData['item_name'] = $itemName ?: $finding->finding_item;
        $findingData['subitem_name'] = $subItemName ?: $finding->finding_subitem;
        $findingData['status_label'] = $finding->completed_at || strtoupper((string) $finding->status) === 'C'
            ? 'Close'
            : 'Open';

        $attachments = $this->findingAttachments($findingId);
        $commentRows = TrFindingActivity::query()
            ->where('finding_id', $findingId)
            ->where('status_activity', 'COMMENT')
            ->where(fn (Builder $query) => $query->whereNull('status')->orWhere('status', '<>', 'X'))
            ->orderBy('activity_date')
            ->orderBy('id')
            ->get(['id', 'activity_descr', 'activity_date', 'created_by']);
        $comments = $commentRows->map(fn (TrFindingActivity $comment) => [
                'id' => $comment->id,
                'comment' => $comment->activity_descr,
                'created_by' => $comment->created_by,
                'created_at' => $comment->activity_date?->format('d M Y H:i'),
                'attachments' => $attachments
                    ->where('doctype', 'FDC-'.$comment->id)
                    ->values(),
            ]);
        $photoAttachments = $attachments
            ->reject(fn ($attachment) => Str::startsWith($attachment['doctype'], 'FDC'))
            ->values();

        return response()->json([
            'data' => $findingData,
            'attachments' => $photoAttachments,
            'comments' => $comments,
        ]);
    }

    public function storeComment(Request $request, string $findingId)
    {
        $finding = TrFinding::query()
            ->where('finding_id', $findingId)
            ->whereIn('cpny_id', $this->companies(Auth::user()->username))
            ->firstOrFail();
        $validated = $request->validate([
            'comment' => ['required', 'string', 'max:5000'],
            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => ['file', 'max:10240'],
        ]);

        $comment = TrFindingActivity::query()->create([
            'finding_id' => $findingId,
            'activity_date' => now(),
            'activity_descr' => $validated['comment'],
            'status_activity' => 'COMMENT',
            'status' => 'A',
            'created_by' => Auth::user()->username,
        ]);

        if ($request->hasFile('attachments')) {
            app(TrAttachmentController::class)->uploadInternal([
                'refnbr' => $findingId,
                'doctype' => 'FDC-'.$comment->id,
                'cpny_id' => $finding->cpny_id,
                'department_id' => $finding->department_id,
                'base_folder' => 'att-purchasing-app/finding-comment',
                'created_by' => Auth::user()->username,
            ], $request->file('attachments'));
        }

        return response()->json(['message' => 'Comment added successfully.']);
    }

    private function companies(string $username)
    {
        return Usercpny::query()
            ->where('username', $username)
            ->where('status', 'A')
            ->orderBy('cpny_id')
            ->pluck('cpny_id')
            ->unique()
            ->values();
    }

    private function closed(Builder $query): Builder
    {
        return $query->where(function (Builder $subQuery) {
            $subQuery->where('status', 'C')->orWhereNotNull('completed_at');
        });
    }

    private function open(Builder $query): Builder
    {
        return $query->whereNull('completed_at')
            ->where(function (Builder $subQuery) {
                $subQuery->whereNull('status')->orWhere('status', '<>', 'C');
            });
    }

    private function findingAttachments(string $findingId)
    {
        $rows = TrAttachment::query()
            ->where('refnbr', $findingId)
            ->where('status', 'A')
            ->orderBy('id')
            ->get();

        if ($rows->isEmpty()) {
            return collect();
        }

        $config = config('filesystems.disks.gcs');
        $keyFilePath = $config['key_file'];

        if (!Str::startsWith($keyFilePath, ['/', 'C:\\', 'D:\\'])) {
            $keyFilePath = base_path($keyFilePath);
        }

        $bucket = (new StorageClient([
            'projectId' => $config['project_id'],
            'keyFilePath' => $keyFilePath,
        ]))->bucket($config['bucket']);

        return $rows->map(function (TrAttachment $attachment) use ($bucket) {
            $url = null;

            try {
                $url = $bucket->object(
                    rtrim((string) $attachment->folder, '/').'/'.$attachment->filename
                )->signedUrl(new \DateTimeImmutable('+10 minutes'), ['version' => 'v4']);
            } catch (\Throwable) {
                // The attachment metadata remains visible when the storage link is unavailable.
            }

            $extension = ltrim(strtolower((string) $attachment->extention), '.');

            return [
                'id' => $attachment->id,
                'doctype' => strtoupper((string) $attachment->doctype),
                'name' => $attachment->attachment_name ?: $attachment->filename,
                'extension' => $extension,
                'url' => $url,
                'is_image' => in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'], true),
            ];
        })->values();
    }
}
