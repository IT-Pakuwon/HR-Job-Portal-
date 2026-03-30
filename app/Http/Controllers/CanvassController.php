<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\HasAutonbr;
use App\Models\Autonbr;
use App\Models\Bq;
use App\Models\BudgetDetail;
use App\Models\MsCompany;
use App\Models\MsPurchSetting;
use App\Models\MsTop;
use App\Models\TrApproval;
use App\Models\TrAttachment;
use App\Models\TrBQCS;
use App\Models\TrCS;
use App\Models\TrCSdetail;
use App\Models\TrIMBudget;
use App\Models\TrKontrak;
use App\Models\TrPO;
use App\Models\TrPOdetail;
use App\Models\TrPoLastPrice;
use App\Models\TrPOReuse;
use App\Models\TrSPPB;
use App\Models\TrSPPBdetail;
use App\Models\TrSPPJ;
use App\Models\TrSPPJdetail;
use App\Models\TrSPPK;
use App\Models\TrSPPKdetail;
use App\Models\TrSPPT;
use App\Models\TrSPPTdetail;
use App\Models\User;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Vinkla\Hashids\Facades\Hashids;

class CanvassController extends Controller
{
    use HasAutonbr;

    public function createCS(string $doc, string $hash)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $src = \Hashids::decode($hash)[0] ?? null;
        abort_if(!$src, 404);

        $doc = strtoupper($doc);
        abort_unless(in_array($doc, ['SPPB', 'SPPJ', 'SPPK', 'SPPT'], true), 404, 'Invalid doc type');

        $header = null;
        $detail = collect();
        $docno = null;
        $refnbr = null;
        $top_type = 'PO';
        $sourceShowUrl = null;
        $prefix2 = null; // keep for blade compatibility

        switch ($doc) {
            case 'SPPB':
                $header = TrSPPB::with([
                    'requestType:requesttypeid,requesttype_name',
                    'creator:username,name',
                    'purchaser:username,name',
                ])->findOrFail($src);

                $detail = TrSPPBdetail::where('sppbid', $header->sppbid)
                    ->orderBy('sppb_no', 'asc')->get();

                $refnbr = $header->sppbid;
                $docno = $header->sppbno ?? $header->doc_no ?? $header->sppbid;
                $top_type = 'PO';
                $sourceShowUrl = url('/showsppbs/'.$hash);
                break;

            case 'SPPJ':
                $header = TrSPPJ::with([
                    'requestType:requesttypeid,requesttype_name',
                    'creator:username,name',
                    'purchaser:username,name',
                ])->findOrFail($src);

                $detail = TrSPPJdetail::where('sppjid', $header->sppjid)
                    ->orderBy('sppj_no', 'asc')->get();

                $refnbr = $header->sppjid;
                $docno = $header->sppjno ?? $header->doc_no ?? $header->sppjid;
                $top_type = 'SPK';
                $sourceShowUrl = url('/showsppjs/'.$hash);
                break;

            case 'SPPK':
                $header = TrSPPK::with([
                    'requestType:requesttypeid,requesttype_name',
                    'creator:username,name',
                    'purchaser:username,name',
                ])->findOrFail($src);

                $detail = TrSPPKdetail::where('sppkid', $header->sppkid)
                    ->orderBy('sppk_no', 'asc')->get();

                $refnbr = $header->sppkid;
                $docno = $header->sppkno ?? $header->doc_no ?? $header->sppkid;
                $top_type = 'SPK';
                $sourceShowUrl = url('/showsppks/'.$hash);
                break;

            case 'SPPT':
                $header = TrSPPT::with([
                    'requestType:requesttypeid,requesttype_name',
                    'creator:username,name',
                    'purchaser:username,name',
                ])->findOrFail($src);

                $detail = TrSPPTdetail::where('spptid', $header->spptid)
                    ->orderBy('sppt_no', 'asc')->get();

                $refnbr = $header->spptid;
                $docno = $header->spptno ?? $header->doc_no ?? $header->spptid;
                $top_type = 'SPK';
                $sourceShowUrl = url('/showsppts/'.$hash);
                break;
        }

        // ===== Attachments by refnbr =====
        $rows = TrAttachment::where('refnbr', $refnbr)
            ->where('status', 'A')
            ->orderBy('created_at', 'desc')
            ->get();

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

        $attachments = $rows->map(function ($r) use ($bucket) {
            $objectPath = rtrim($r->folder, '/').'/'.$r->filename;
            $object = $bucket->object($objectPath);

            $signedUrl = null;
            try {
                $signedUrl = $object->signedUrl(
                    new \DateTimeImmutable('+10 minutes'),
                    ['version' => 'v4']
                );
            } catch (\Throwable $e) {
                \Log::warning('Signed URL gagal', ['path' => $objectPath, 'error' => $e->getMessage()]);
            }

            return (object) [
                'display_name' => $r->attachment_name,
                'created_by' => $r->created_by,
                'created_at' => $r->created_at,
                'url' => $signedUrl,
                'folder' => $r->folder,
                'filename' => $r->filename,
                'extention' => $r->extention,
                'size' => $r->filesize,
            ];
        });

        // ===== Items + last price =====
        $items = $this->mapRemainingLines($detail);

        $invIds = collect($items)->pluck('inventoryid')->filter()->unique()->values()->all();

        $lastUnitcostMap = [];
        if (!empty($invIds)) {
            $lpRows = TrPoLastPrice::query()
                ->select('inventoryid', 'unitcost', 'podate', 'created_at')
                ->whereIn('inventoryid', $invIds)
                ->whereNull('deleted_at')
                ->orderByDesc('podate')
                ->orderByDesc('created_at')
                ->get();

            $lastUnitcostMap = $lpRows
                ->groupBy('inventoryid')
                ->map(fn ($g) => (float) ($g->first()->unitcost ?? 0))
                ->toArray();
        }

        $items = collect($items)->map(function ($it) use ($lastUnitcostMap) {
            $invId = $it->inventoryid ?? null;
            $it->last_unitcost = $invId ? ($lastUnitcostMap[$invId] ?? 0) : 0;

            return $it;
        });

        $tops = MsTop::where('status', 'A')
            ->where('top_type', $top_type)
            ->orderByRaw('COALESCE(top_days, 9999), top_name')
            ->get(['topid', 'top_name', 'top_days', 'top_type']);

        return view('pages.canvass.createcs', [
            'doc' => $doc,
            'src_id' => $src,
            'docno' => $docno,
            'header' => $header,
            'attachment' => $attachments,
            'items' => $items,
            'tops' => $tops,
            'poHeader' => null,      // keep blade safe
            'prefix2' => $prefix2,  // keep blade safe
            'sourceShowUrl' => $sourceShowUrl,
            'refnbr' => $refnbr,
        ]);
    }

    public function createCSReuse(string $doc, string $hash)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $src = \Hashids::decode($hash)[0] ?? null;
        abort_if(!$src, 404);

        $doc = 'PO';

        $header = null;
        $detail = collect();
        $docno = null;
        $refnbr = null;
        $top_type = 'PO';
        $sourceShowUrl = null;
        $prefix2 = null;

        $bqid = null;
        $bqtype = null;
        $userPeminta = null;

        $poHeader = null;
        $reuseFirst = TrPOReuse::query()->find($src);

        $isKontrak = false;
        $kontrakHeader = null;

        $budgetPerpost = null;
        $prevCsid = null;

        // =========================================================
        // helper: ambil bqid/bqtype + user peminta DARI SPPJ/SPPT SAJA
        // (karena hanya mereka yang punya bqid/bqtype)
        // =========================================================
        $fillBqFromSppjSppt = function (?string $key) use (&$bqid, &$bqtype, &$userPeminta) {
            if (!$key) {
                return;
            }

            // --- SPPJ ---
            if (Str::startsWith($key, 'PJ')) {
                $pj = TrSPPJ::query()
                    ->select(['bqid', 'bqtype', 'created_by'])
                    ->where('sppjid', $key)
                    ->first();

                if ($pj) {
                    $bqid = $pj->bqid ?? $bqid;
                    $bqtype = $pj->bqtype ?? $bqtype;
                    if (!empty($pj->created_by)) {
                        $userPeminta = $pj->created_by;
                    }
                }

                return;
            }

            // --- SPPT ---
            if (Str::startsWith($key, 'PT')) {
                // ✅ aman: hanya select bqid/bqtype kalau kolomnya ada
                $m = new TrSPPT();
                $table = $m->getTable();
                $conn = $m->getConnectionName() ?: config('database.default');

                $cols = ['created_by'];
                if (Schema::connection($conn)->hasColumn($table, 'bqid')) {
                    $cols[] = 'bqid';
                }
                if (Schema::connection($conn)->hasColumn($table, 'bqtype')) {
                    $cols[] = 'bqtype';
                }

                $pt = TrSPPT::query()
                    ->select($cols)
                    ->where('spptid', $key)
                    ->first();

                if ($pt) {
                    if (property_exists($pt, 'bqid')) {
                        $bqid = $pt->bqid ?? $bqid;
                    }
                    if (property_exists($pt, 'bqtype')) {
                        $bqtype = $pt->bqtype ?? $bqtype;
                    }
                    if (!empty($pt->created_by)) {
                        $userPeminta = $pt->created_by;
                    }
                }

                return;
            }

            // kalau PB/PK atau prefix lain -> tidak ada bqid/bqtype, skip
            return;
        };

        // =========================================================
        // helper: ambil budget_perpost/perpost dari sumber PB/PJ/PK/PT
        // =========================================================
        $fillBudgetPerpostFromSource = function (?string $key) use (&$budgetPerpost) {
            if (!$key) {
                return;
            }

            $try = function (string $modelClass, string $keyCol, string $keyVal) use (&$budgetPerpost) {
                $m = new $modelClass();
                $table = $m->getTable();
                $conn = $m->getConnectionName() ?: config('database.default');

                $cols = [];
                if (Schema::connection($conn)->hasColumn($table, 'budget_perpost')) {
                    $cols[] = 'budget_perpost';
                }
                if (Schema::connection($conn)->hasColumn($table, 'perpost')) {
                    $cols[] = 'perpost';
                }
                if (empty($cols)) {
                    return false;
                }

                $row = $modelClass::query()->select($cols)->where($keyCol, $keyVal)->first();
                if (!$row) {
                    return false;
                }

                if (in_array('budget_perpost', $cols, true) && !empty($row->budget_perpost)) {
                    $budgetPerpost = $row->budget_perpost;

                    return true;
                }
                if (in_array('perpost', $cols, true) && !empty($row->perpost)) {
                    $budgetPerpost = $row->perpost;

                    return true;
                }

                return false;
            };

            if (Str::startsWith($key, 'PB')) {
                if ($try(TrSPPB::class, 'sppbid', $key)) {
                    return;
                }
            }
            if (Str::startsWith($key, 'PJ')) {
                if ($try(TrSPPJ::class, 'sppjid', $key)) {
                    return;
                }
            }
            if (Str::startsWith($key, 'PK')) {
                if ($try(TrSPPK::class, 'sppkid', $key)) {
                    return;
                }
            }
            if (Str::startsWith($key, 'PT')) {
                if ($try(TrSPPT::class, 'spptid', $key)) {
                    return;
                }
            }

            $try(TrSPPB::class, 'sppbid', $key)
            || $try(TrSPPJ::class, 'sppjid', $key)
            || $try(TrSPPK::class, 'sppkid', $key)
            || $try(TrSPPT::class, 'spptid', $key);
        };

        // =========================================================
        // helper: prev csid
        // =========================================================
        $findPrevCsid = function (?string $ref) use (&$prevCsid) {
            if (!$ref) {
                return;
            }

            $prevCsid = TrCS::query()
                ->where('sppbjktid', $ref) // kalau kolomnya refnbr -> ganti
                ->orderByDesc('created_at')
                ->value('csid');
        };

        // =========================================================
        // Resolve source: Reuse / PO / Kontrak
        // =========================================================
        if ($reuseFirst) {
            $ponbrKey = $reuseFirst->ponbr;

            $kontrakHeader = TrKontrak::query()->where('kontrakid', $ponbrKey)->first();
            if ($kontrakHeader) {
                $isKontrak = true;
            } else {
                $poHeader = TrPO::with(['creator:username,name'])
                    ->where('ponbr', $ponbrKey)
                    ->first();
                abort_if(!$poHeader, 404);
            }
        } else {
            $poHeader = TrPO::with(['creator:username,name'])->findOrFail($src);
        }

        // =========================================================
        // KONTRAK MODE
        // =========================================================
        if ($isKontrak) {
            $header = $kontrakHeader;

            $srcKey = $kontrakHeader->sppbjktid ?? null;

            // bqid/bqtype + user peminta hanya dari PJ/PT
            if ($srcKey && Str::startsWith($srcKey, ['PJ', 'PT'])) {
                $fillBqFromSppjSppt($srcKey);
            }

            // budget perpost dari PB/PJ/PK/PT
            $fillBudgetPerpostFromSource($srcKey);

            // fallback user peminta kalau PJ/PT tidak ketemu
            if (!$userPeminta) {
                $userPeminta = $kontrakHeader->created_by ?? null;
            }

            // refnbr untuk attachment + prev cs
            $refnbr = (string) ($srcKey ?: $kontrakHeader->kontrakid);

            $findPrevCsid($refnbr);

            $docno = (string) $kontrakHeader->kontrakid;
            $top_type = 'PO';

            $detail = TrPOReuse::query()
                ->where('ponbr', $kontrakHeader->kontrakid)
                ->where('cpny_id', $kontrakHeader->cpny_id)
                ->where(function ($q) {
                    $q->whereNull('openordered')->orWhere('openordered', '>', 0);
                })
                ->orderBy('id', 'asc')
                ->get();

            $poHeader = null;
            $prefix2 = null;
        } else {
            // =========================================================
            // PO NORMAL MODE
            // =========================================================
            $header = $poHeader;

            $sppbjktid = $poHeader->sppbjktid ?? null;

            // bqid/bqtype hanya PJ/PT
            if ($sppbjktid && Str::startsWith($sppbjktid, ['PJ', 'PT'])) {
                $fillBqFromSppjSppt($sppbjktid);
            }

            // budget perpost dari sumber
            $fillBudgetPerpostFromSource($sppbjktid);

            if ($sppbjktid) {
                $headerSource = TrSPPB::where('sppbid', $sppbjktid)->first()
                    ?? TrSPPJ::where('sppjid', $sppbjktid)->first()
                    ?? TrSPPK::where('sppkid', $sppbjktid)->first()
                    ?? TrSPPT::where('spptid', $sppbjktid)->first();

                if ($headerSource) {
                    $header = $headerSource;
                }

                if (Str::startsWith($sppbjktid, ['PB', 'PK'])) {
                    $prefix2 = substr($sppbjktid, 0, 2);
                }
            }

            $detail = TrPOReuse::where('ponbr', $poHeader->ponbr)
                ->where('cpny_id', $poHeader->cpny_id)
                ->where(function ($q) {
                    $q->whereNull('openordered')->orWhere('openordered', '>', 0);
                })
                ->orderBy('id', 'asc')
                ->get();

            $refnbr = $poHeader->sppbjktid ?? $poHeader->ponbr;
            $docno = $poHeader->ponbr;
            $top_type = $poHeader->potype ?? 'PO';

            // prev cs utk PO reuse juga boleh (kalau mau)
            $findPrevCsid($refnbr);
        }

        // ===== Attachments =====
        $rows = TrAttachment::where('refnbr', $refnbr)
            ->where('status', 'A')
            ->orderBy('created_at', 'desc')
            ->get();

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

        $attachments = $rows->map(function ($r) use ($bucket) {
            $objectPath = rtrim($r->folder, '/').'/'.$r->filename;
            $object = $bucket->object($objectPath);

            $signedUrl = null;
            try {
                $signedUrl = $object->signedUrl(
                    new \DateTimeImmutable('+10 minutes'),
                    ['version' => 'v4']
                );
            } catch (\Throwable $e) {
                \Log::warning('Signed URL gagal', ['path' => $objectPath, 'error' => $e->getMessage()]);
            }

            return (object) [
                'display_name' => $r->attachment_name,
                'created_by' => $r->created_by,
                'created_at' => $r->created_at,
                'url' => $signedUrl,
                'folder' => $r->folder,
                'filename' => $r->filename,
                'extention' => $r->extention,
                'size' => $r->filesize,
            ];
        });

        // ===== Items + last price =====
        $items = $this->mapRemainingLines($detail);

        $invIds = collect($items)->pluck('inventoryid')->filter()->unique()->values()->all();

        $lastUnitcostMap = [];
        if (!empty($invIds)) {
            $lpRows = TrPoLastPrice::query()
                ->select('inventoryid', 'unitcost', 'podate', 'created_at')
                ->whereIn('inventoryid', $invIds)
                ->whereNull('deleted_at')
                ->orderByDesc('podate')
                ->orderByDesc('created_at')
                ->get();

            $lastUnitcostMap = $lpRows
                ->groupBy('inventoryid')
                ->map(fn ($g) => (float) ($g->first()->unitcost ?? 0))
                ->toArray();
        }

        $items = collect($items)->map(function ($it) use ($lastUnitcostMap) {
            $invId = $it->inventoryid ?? null;
            $it->last_unitcost = $invId ? ($lastUnitcostMap[$invId] ?? 0) : 0;

            return $it;
        });

        $tops = MsTop::where('status', 'A')
            ->where('top_type', $top_type)
            ->orderByRaw('COALESCE(top_days, 9999), top_name')
            ->get(['topid', 'top_name', 'top_days', 'top_type']);

        return view('pages.canvass.createcs', [
            'doc' => $doc,
            'src_id' => $src,
            'docno' => $docno,
            'header' => $header,
            'attachment' => $attachments,
            'items' => $items,
            'tops' => $tops,
            'poHeader' => $poHeader,
            'prefix2' => $prefix2,
            'sourceShowUrl' => $sourceShowUrl,
            'refnbr' => $refnbr,
            'bqid' => $bqid,
            'bqtype' => $bqtype,
            'user_peminta' => $userPeminta,
            'budget_perpost' => $budgetPerpost,
            'prev_csid' => $prevCsid,
        ]);
    }

    public function createCSReuse_xxx(string $doc, string $hash)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $src = \Hashids::decode($hash)[0] ?? null;
        abort_if(!$src, 404);

        $doc = 'PO';

        $header = null;
        $detail = collect();
        $docno = null;
        $refnbr = null;
        $top_type = 'PO';
        $sourceShowUrl = null; // PO biasanya tidak ada show URL SPPB/J/K/T
        $prefix2 = null;
        $bqid = null;
        $bqtype = null;
        $userPeminta = null;

        // src dari hash bisa: id TrPO (normal) atau id TrPOReuse
        $poHeader = null;
        $reuseFirst = TrPOReuse::query()->find($src);
        $isKontrak = false;
        $kontrakHeader = null;
        $budgetPerpost = null;
        $prevCsid = null;

        $fillBudgetPerpostFromSource = function (?string $key) use (&$budgetPerpost) {
            if (!$key) {
                return;
            }

            $try = function (string $modelClass, string $keyCol, string $keyVal) use (&$budgetPerpost) {
                $m = new $modelClass();
                $table = $m->getTable();
                $conn = $m->getConnectionName() ?: config('database.default');

                // beberapa table pakai budget_perpost, ada yg pakai perpost
                $cols = [];
                if (Schema::connection($conn)->hasColumn($table, 'budget_perpost')) {
                    $cols[] = 'budget_perpost';
                }
                if (Schema::connection($conn)->hasColumn($table, 'perpost')) {
                    $cols[] = 'perpost';
                }

                if (empty($cols)) {
                    return false;
                }

                $row = $modelClass::query()->select($cols)->where($keyCol, $keyVal)->first();
                if (!$row) {
                    return false;
                }

                if (in_array('budget_perpost', $cols, true) && !empty($row->budget_perpost)) {
                    $budgetPerpost = $row->budget_perpost;

                    return true;
                }
                if (in_array('perpost', $cols, true) && !empty($row->perpost)) {
                    $budgetPerpost = $row->perpost;

                    return true;
                }

                return false;
            };

            // urutan sesuai prefix (lebih cepat), tapi aman kalau tidak match
            if (Str::startsWith($key, 'PB')) {
                if ($try(TrSPPB::class, 'sppbid', $key)) {
                    return;
                }
            }
            if (Str::startsWith($key, 'PJ')) {
                if ($try(TrSPPJ::class, 'sppjid', $key)) {
                    return;
                }
            }
            if (Str::startsWith($key, 'PK')) {
                if ($try(TrSPPK::class, 'sppkid', $key)) {
                    return;
                }
            }
            if (Str::startsWith($key, 'PT')) {
                if ($try(TrSPPT::class, 'spptid', $key)) {
                    return;
                }
            }

            // fallback scan (kalau prefix tidak standar)
            $try(TrSPPB::class, 'sppbid', $key)
            || $try(TrSPPJ::class, 'sppjid', $key)
            || $try(TrSPPK::class, 'sppkid', $key)
            || $try(TrSPPT::class, 'spptid', $key);
        };

        $findPrevCsid = function (?string $ref) use (&$prevCsid) {
            if (!$ref) {
                return;
            }

            $prevCsid = TrCS::query()
                ->where('sppbjktid', $ref)
                ->orderByDesc('created_at')
                ->value('csid');
        };

        if ($reuseFirst) {
            $ponbrKey = $reuseFirst->ponbr;

            $kontrakHeader = TrKontrak::query()->where('kontrakid', $ponbrKey)->first();
            if ($kontrakHeader) {
                $isKontrak = true;
            } else {
                $poHeader = TrPO::with(['creator:username,name'])
                    ->where('ponbr', $ponbrKey)
                    ->first();
                abort_if(!$poHeader, 404);
            }
        } else {
            $poHeader = TrPO::with(['creator:username,name'])->findOrFail($src);
        }

        if ($isKontrak) {
            // =========================
            // KONTRAK MODE
            // =========================
            $header = $kontrakHeader;
            $fillBqFromSppjSppt($kontrakHeader->sppbjktid ?? null);
            if (!$userPeminta) {
                $userPeminta = $kontrakHeader->created_by ?? null; // atau Auth user kalau mau
            }

            // refnbr untuk attachment dan juga lookup prev cs
            $refnbr = (string) ($srcKey ?? $kontrakHeader->kontrakid);

            // prev csid utk kontrak (ambil dari CS sebelumnya yg pakai refnbr yg sama)
            $findPrevCsid($refnbr);

            // NOTE: kalau sppbjktid kontrak null, fallback ke kontrakid (biar attachment tidak null)
            $refnbr = (string) ($kontrakHeader->sppbjktid ?? $kontrakHeader->kontrakid);

            $docno = (string) $kontrakHeader->kontrakid;
            $top_type = 'PO';

            $detail = TrPOReuse::query()
                ->where('ponbr', $kontrakHeader->kontrakid)
                ->where('cpny_id', $kontrakHeader->cpny_id)
                ->where(function ($q) {
                    $q->whereNull('openordered')->orWhere('openordered', '>', 0);
                })
                ->orderBy('id', 'asc')
                ->get();

            $poHeader = null;
            $prefix2 = null;
        } else {
            // =========================
            // PO NORMAL MODE
            // =========================
            $header = $poHeader;

            $sppbjktid = $poHeader->sppbjktid ?? null;
            $fillBqFromSppjSppt($sppbjktid);
            if ($sppbjktid && Str::startsWith($sppbjktid, ['PJ', 'PT'])) {
                $fillBqFromSppjSppt($sppbjktid);
            }

            if ($sppbjktid) {
                $headerSource = TrSPPB::where('sppbid', $sppbjktid)->first()
                    ?? TrSPPJ::where('sppjid', $sppbjktid)->first()
                    ?? TrSPPK::where('sppkid', $sppbjktid)->first()
                    ?? TrSPPT::where('spptid', $sppbjktid)->first();

                if ($headerSource) {
                    $header = $headerSource;
                }

                if (Str::startsWith($sppbjktid, ['PB', 'PK'])) {
                    $prefix2 = substr($sppbjktid, 0, 2);
                }
            }

            $detail = TrPOReuse::where('ponbr', $poHeader->ponbr)
                ->where('cpny_id', $poHeader->cpny_id)
                ->where(function ($q) {
                    $q->whereNull('openordered')->orWhere('openordered', '>', 0);
                })
                ->orderBy('id', 'asc')
                ->get();

            $refnbr = $poHeader->sppbjktid ?? $poHeader->ponbr;
            $docno = $poHeader->ponbr;
            $top_type = $poHeader->potype ?? 'PO';
        }

        // ===== Attachments =====
        $rows = TrAttachment::where('refnbr', $refnbr)
            ->where('status', 'A')
            ->orderBy('created_at', 'desc')
            ->get();

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

        $attachments = $rows->map(function ($r) use ($bucket) {
            $objectPath = rtrim($r->folder, '/').'/'.$r->filename;
            $object = $bucket->object($objectPath);

            $signedUrl = null;
            try {
                $signedUrl = $object->signedUrl(
                    new \DateTimeImmutable('+10 minutes'),
                    ['version' => 'v4']
                );
            } catch (\Throwable $e) {
                \Log::warning('Signed URL gagal', ['path' => $objectPath, 'error' => $e->getMessage()]);
            }

            return (object) [
                'display_name' => $r->attachment_name,
                'created_by' => $r->created_by,
                'created_at' => $r->created_at,
                'url' => $signedUrl,
                'folder' => $r->folder,
                'filename' => $r->filename,
                'extention' => $r->extention,
                'size' => $r->filesize,
            ];
        });

        // ===== Items + last price =====
        $items = $this->mapRemainingLines($detail);

        $invIds = collect($items)->pluck('inventoryid')->filter()->unique()->values()->all();

        $lastUnitcostMap = [];
        if (!empty($invIds)) {
            $lpRows = TrPoLastPrice::query()
                ->select('inventoryid', 'unitcost', 'podate', 'created_at')
                ->whereIn('inventoryid', $invIds)
                ->whereNull('deleted_at')
                ->orderByDesc('podate')
                ->orderByDesc('created_at')
                ->get();

            $lastUnitcostMap = $lpRows
                ->groupBy('inventoryid')
                ->map(fn ($g) => (float) ($g->first()->unitcost ?? 0))
                ->toArray();
        }

        $items = collect($items)->map(function ($it) use ($lastUnitcostMap) {
            $invId = $it->inventoryid ?? null;
            $it->last_unitcost = $invId ? ($lastUnitcostMap[$invId] ?? 0) : 0;

            return $it;
        });

        $tops = MsTop::where('status', 'A')
            ->where('top_type', $top_type)
            ->orderByRaw('COALESCE(top_days, 9999), top_name')
            ->get(['topid', 'top_name', 'top_days', 'top_type']);

        return view('pages.canvass.createcs', [
            'doc' => $doc,
            'src_id' => $src,
            'docno' => $docno,
            'header' => $header,
            'attachment' => $attachments,
            'items' => $items,
            'tops' => $tops,
            'poHeader' => $poHeader,
            'prefix2' => $prefix2,
            'sourceShowUrl' => $sourceShowUrl,
            'refnbr' => $refnbr,
            'bqid' => $bqid,
            'bqtype' => $bqtype,
            'user_peminta' => $userPeminta,
            'budget_perpost' => $budgetPerpost,
            'prev_csid' => $prevCsid,
        ]);
    }

    public function createCS_old(string $doc, string $hash)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $src = \Hashids::decode($hash)[0] ?? null;
        abort_if(!$src, 404);

        $doc = strtoupper($doc);
        abort_unless(in_array($doc, ['SPPB', 'SPPJ', 'SPPK', 'SPPT', 'PO']), 404, 'Invalid doc type');

        $header = null;
        $detail = collect();
        $docno = null;

        switch ($doc) {
            case 'SPPB':
                $header = TrSPPB::with([
                    'requestType:requesttypeid,requesttype_name',
                    'creator:username,name',
                    'purchaser:username,name',
                ])->findOrFail($src);
                $detail = TrSPPBdetail::where('sppbid', $header->sppbid)
                            ->orderBy('sppb_no', 'asc')->get();
                $refnbr = $header->sppbid;          // <-- pakai sebagai refnbr di TrAttachment
                $docno = $header->sppbno ?? $header->doc_no ?? $header->sppbid;
                $top_type = 'PO';
                break;

            case 'SPPJ':
                $header = TrSPPJ::with([
                    'requestType:requesttypeid,requesttype_name',
                    'creator:username,name',
                    'purchaser:username,name',
                ])->findOrFail($src);
                $detail = TrSPPJdetail::where('sppjid', $header->sppjid)
                            ->orderBy('sppj_no', 'asc')->get();
                $refnbr = $header->sppjid;
                $docno = $header->sppjno ?? $header->doc_no ?? $header->sppjid;
                $top_type = 'SPK';
                break;

            case 'SPPK':
                $header = TrSPPK::with([
                    'requestType:requesttypeid,requesttype_name',
                    'creator:username,name',
                    'purchaser:username,name',
                ])->findOrFail($src);
                $detail = TrSPPKdetail::where('sppkid', $header->sppkid)
                            ->orderBy('sppk_no', 'asc')->get();
                $refnbr = $header->sppkid;
                $docno = $header->sppkno ?? $header->doc_no ?? $header->sppkid;
                $top_type = 'SPK';
                break;

            case 'SPPT':
                $header = TrSPPT::with([
                    'requestType:requesttypeid,requesttype_name',
                    'creator:username,name',
                    'purchaser:username,name',
                ])->findOrFail($src);
                $detail = TrSPPTdetail::where('spptid', $header->spptid)
                            ->orderBy('sppt_no', 'asc')->get();
                $refnbr = $header->spptid;
                $docno = $header->spptno ?? $header->doc_no ?? $header->spptid;
                $top_type = 'SPK';
                break;

            case 'PO':
                // src dari hash bisa:
                // - id TrPO (normal)
                // - id TrPOReuse (kalau kamu pakai eid = id tr_po_reuse)
                // Jadi kita deteksi dulu

                $poHeader = null;
                $reuseFirst = TrPOReuse::query()->find($src); // kalau hash = id tr_po_reuse
                $isKontrak = false;
                $kontrakHeader = null;

                if ($reuseFirst) {
                    // ponbr di tr_po_reuse kamu isi kontrakid atau ponbr
                    $ponbrKey = $reuseFirst->ponbr;

                    // cek apakah ponbrKey itu kontrakid
                    $kontrakHeader = TrKontrak::query()->where('kontrakid', $ponbrKey)->first();
                    if ($kontrakHeader) {
                        $isKontrak = true;
                    } else {
                        // bukan kontrak => anggap ponbrKey adalah ponbr PO
                        $poHeader = TrPO::with(['creator:username,name'])
                            ->where('ponbr', $ponbrKey)
                            ->first();
                    }
                } else {
                    // hash = id TrPO normal
                    $poHeader = TrPO::with(['creator:username,name'])->findOrFail($src);
                }

                if ($isKontrak) {
                    // =========================
                    // KONTRAK MODE
                    // =========================
                    $header = $kontrakHeader;                 // header untuk view createcs

                    $refnbr = (string) $kontrakHeader->sppbjktid; // attachment ref (sesuaikan kalau memang kontrak pakai ref ini)

                    $docno = (string) $kontrakHeader->kontrakid; // tampilkan docno
                    $top_type = 'PO';
                    // $doc = 'KONTRAK'; // override doc untuk keperluan view (kalau kamu butuh)

                    // detail dari tr_po_reuse where ponbr = kontrakid
                    $detail = TrPOReuse::query()
                        ->where('ponbr', $kontrakHeader->kontrakid)
                        ->where('cpny_id', $kontrakHeader->cpny_id)
                        ->where(function ($q) {
                            $q->whereNull('openordered')->orWhere('openordered', '>', 0);
                        })
                        ->orderBy('id', 'asc')
                        ->get();

                    // optional: supaya view masih punya poHeader variable (kalau blade butuh)
                    $poHeader = null;

                    // optional prefix2 (kalau kamu perlu)
                    $prefix2 = null;
                } else {
                    // =========================
                    // PO NORMAL MODE (logic kamu tetap)
                    // =========================
                    $header = $poHeader;

                    $sppbjktid = $poHeader->sppbjktid ?? null;

                    if ($sppbjktid) {
                        $headerSource = TrSPPB::where('sppbid', $sppbjktid)->first()
                            ?? TrSPPJ::where('sppjid', $sppbjktid)->first()
                            ?? TrSPPK::where('sppkid', $sppbjktid)->first()
                            ?? TrSPPT::where('spptid', $sppbjktid)->first();

                        if ($headerSource) {
                            $header = $headerSource;
                        }
                    }

                    $detail = TrPOReuse::where('ponbr', $poHeader->ponbr)
                        ->where('cpny_id', $poHeader->cpny_id)
                        ->where(function ($q) {
                            $q->whereNull('openordered')
                            ->orWhere('openordered', '>', 0);
                        })
                        ->orderBy('id', 'asc')
                        ->get();

                    $refnbr = $poHeader->sppbjktid ?? $poHeader->ponbr;
                    $docno = $poHeader->ponbr;
                    $top_type = $poHeader->potype ?? 'PO';

                    $sppbjktid = $poHeader->sppbjktid;

                    if (Str::startsWith($sppbjktid, ['PB', 'PK'])) {
                        $prefix2 = substr($sppbjktid, 0, 2);
                    } else {
                        $prefix2 = null;
                    }
                }

                break;
        }

        $sourceShowUrl = null;
        switch ($doc) {
            case 'SPPB':
                $sourceShowUrl = url('/showsppbs/'.$hash);
                break;
            case 'SPPJ':
                $sourceShowUrl = url('/showsppjs/'.$hash);
                break;
            case 'SPPK':
                $sourceShowUrl = url('/showsppks/'.$hash);
                break;
            case 'SPPT':
                $sourceShowUrl = url('/showsppts/'.$hash);
                break;
        }

        // ===== Ambil lampiran dari TrAttachment (berdasarkan refnbr) =====
        $rows = TrAttachment::where('refnbr', $refnbr)
            ->where('status', 'A')
            ->orderBy('created_at', 'desc')
            ->get();

        // ===== Siapkan Signed URL dari GCS (private) =====
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

        // Map ke bentuk siap pakai di view "createcs"
        $attachments = $rows->map(function ($r) use ($bucket) {
            $objectPath = rtrim($r->folder, '/').'/'.$r->filename; // contoh: att-purchasing-app/wo/2025/xxx.pdf
            $object = $bucket->object($objectPath);

            $signedUrl = null;
            try {
                $signedUrl = $object->signedUrl(
                    new \DateTimeImmutable('+10 minutes'),
                    ['version' => 'v4']
                );
            } catch (\Throwable $e) {
                \Log::warning('Signed URL gagal', ['path' => $objectPath, 'error' => $e->getMessage()]);
            }

            return (object) [
                'display_name' => $r->attachment_name,
                'created_by' => $r->created_by,
                'created_at' => $r->created_at,
                'url' => $signedUrl, // bisa null jika gagal
                'folder' => $r->folder,
                'filename' => $r->filename,
                'extention' => $r->extention,
                'size' => $r->filesize,
            ];
        });

        // Map detail (logika kamu sendiri)
        $items = $this->mapRemainingLines($detail);

        // ambil inventoryid yang ada di items
        $invIds = collect($items)
            ->pluck('inventoryid')
            ->filter()
            ->unique()
            ->values()
            ->all();

        // map: inventoryid => latest unitcost
        $lastUnitcostMap = [];

        if (!empty($invIds)) {
            $rows = TrPoLastPrice::query()
                ->select('inventoryid', 'unitcost', 'podate', 'created_at') // unitcost wajib, lainnya hanya untuk orderBy
                ->whereIn('inventoryid', $invIds)
                ->whereNull('deleted_at')
                ->orderByDesc('podate')       // terbaru
                ->orderByDesc('created_at')   // tie breaker
                ->get();

            // ambil baris pertama (latest) untuk tiap inventoryid
            $lastUnitcostMap = $rows
                ->groupBy('inventoryid')
                ->map(fn ($g) => (float) ($g->first()->unitcost ?? 0))
                ->toArray();
        }

        // inject ke tiap item (biar gampang dipakai di blade)
        $items = collect($items)->map(function ($it) use ($lastUnitcostMap) {
            $invId = $it->inventoryid ?? null;
            $it->last_unitcost = $invId ? ($lastUnitcostMap[$invId] ?? 0) : 0;

            return $it;
        });

        $tops = MsTop::where('status', 'A')
            ->where('top_type', $top_type)
            ->orderByRaw('COALESCE(top_days, 9999), top_name')
            ->get(['topid', 'top_name', 'top_days', 'top_type']);

        return view('pages.canvass.createcs', [
            'doc' => $doc,
            'src_id' => $src,
            'docno' => $docno,
            'header' => $header,
            'attachment' => $attachments, // tetap pakai key 'attachment' agar Blade lama aman
            'items' => $items,
            'tops' => $tops,
            'poHeader' => $poHeader ?? null,
            'prefix2' => $prefix2 ?? null,
            'sourceShowUrl' => $sourceShowUrl,
            'refnbr' => $refnbr,
        ]);
    }

    private function mapRemainingLines($detail)
    {
        return $detail->map(function ($row) {
            // Deteksi apakah ini baris dari TrPOReuse
            $isPoReuse = $row instanceof TrPOReuse;

            // --- 1. Ambil angka dasar ---
            // Untuk PO Reuse, biasanya qty utama ada di base_qty atau qty
            if ($isPoReuse) {
                $qtyTotal = (float) ($row->base_qty ?? $row->qty ?? 0);
            } else {
                $qtyTotal = (float) ($row->qty ?? 0);
            }

            $ordered = (float) ($row->ordered ?? 0);
            $rejected = (float) ($row->rejectordered ?? 0);
            // Di model TrPOReuse: 'completedordered' (bukan completeordered),
            // jadi kita cover dua-duanya biar aman.
            $completed = (float) ($row->completeordered ?? $row->completedordered ?? 0);

            // --- 2. Hitung remaining / open ---
            if (isset($row->openordered) && $row->openordered !== null) {
                // Kalau sudah ada kolom openordered → itu yang kita anggap "sisa" yang boleh dipakai
                $remaining = (float) $row->openordered;
            } else {
                // Kalau tidak ada openordered, hitung manual
                $remaining = max($qtyTotal - $ordered - $rejected - $completed, 0);
            }

            // --- 3. Set qty yang akan dipakai di create CS ---
            // createcs view biasanya pakai $row->qty sebagai "sisa" yang bisa diinput.
            $row->qty = $remaining;

            // --- 4. Sinkronkan base_qty (kalau ada base_multiplier) ---
            if (isset($row->base_multiplier) && is_numeric($row->base_multiplier)) {
                $row->base_qty = round($remaining * (float) $row->base_multiplier, 3);
            } elseif (!isset($row->base_qty)) {
                // fallback: kalau tidak ada base_multiplier, set base_qty = qty
                $row->base_qty = $remaining;
            }

            return $row;
        })
        ->filter(function ($row) {
            // hanya kembalikan baris yang masih punya sisa > 0
            return (float) $row->qty > 0;
        })
        ->values();
    }

    public function storeCS(Request $request)
    {
        // dd($request->all());
        // dd('ini store CS');
        // 1) Validasi payload dasar (store = langsung submit)
        $request->validate([
            'doc' => 'required|string',     // SPPB|SPPJ|SPPK|SPPT atau lain (revisi)
            'src_id' => 'required',            // sumber doc wajib ada untuk submit
            'sppbjktid' => 'nullable|string',
            'cpny_id' => 'required|string',
            'department_id' => 'required|string',
            'bqid' => 'nullable|string',
            'user_peminta' => 'nullable|string',
            'csnote' => 'nullable|string',
            'assigndate' => 'nullable|string',
            'vendors' => 'required|string', // JSON array
            'details' => 'required|string', // JSON array
            // tidak pakai 'action' di sini, selalu submit (mode lengkap)
        ]);

        // 2) Ambil & decode input
        $doc = strtoupper($request->input('doc'));
        $srcId = $request->input('src_id');
        $sppbjktid = $request->input('sppbjktid');
        $cpnyId = $request->input('cpny_id');
        $deptId = $request->input('department_id');
        $bqid = $request->input('bqid');
        $userPeminta = $request->input('user_peminta');
        $csnote = $request->input('csnote');
        $assigndate = $request->input('assigndate');
        $prev_csid = $request->input('prev_csid');   // kalau ada → CS revisi
        $spbid = $request->input('spbid');
        $woid = $request->input('woid');
        $keperluan = $request->input('keperluan');
        $bqtype = $request->input('bqtype');
        $budgetPerpost = $request->input('budget_perpost');

        $vendors = json_decode($request->input('vendors', '[]'), true) ?: [];
        $details = json_decode($request->input('details', '[]'), true) ?: [];

        $round2 = fn ($n) => round((float) $n, 2);
        $docSelectedGrand = collect($vendors)->sum(function ($v) use ($round2) {
            return $round2($v['selected_grand'] ?? 0);
        });

        // === Ambil inventoryid unik dari payload ===
        $invIds = collect($details)
            ->pluck('inventoryid')
            ->filter()
            ->unique()
            ->values()
            ->all();

        // === Ambil harga PO terakhir per inventory ===
        $lastPriceMap = [];

        if (!empty($invIds)) {
            $rows = TrPoLastPrice::query()
                ->select('inventoryid', 'unitcost', 'podate', 'created_at')
                ->whereIn('inventoryid', $invIds)
                ->whereNull('deleted_at')
                ->orderByDesc('podate')
                ->orderByDesc('created_at')
                ->get()
                ->groupBy('inventoryid');

            foreach ($rows as $inventoryid => $items) {
                $lastPriceMap[$inventoryid] = round((float) ($items->first()->unitcost ?? 0), 2);
            }
        }

        // 3) Context user & waktu
        $doctype = 'CS';
        $user = $request->user();
        $username = $user->username ?? 'system';
        $fullname = $user->name ?? 'system';

        $dt = Carbon::now();
        $year = (int) $dt->year;
        $month = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
        $datestamp = $dt->toDateTimeString();

        $safeSet = function ($model, string $table, string $column, $value) {
            if (Schema::connection('pgsql')->hasColumn($table, $column)) {
                $model->{$column} = $value;
            }
        };

        // Hitung rev_csid
        if ($prev_csid) {
            // Ada CS sebelumnya → ini revisi dari CS awal (prev_csid = CS A)
            $lastRev = TrCS::where('prev_csid', $prev_csid)->max('rev_csid');
            $nextRev = $lastRev ? $lastRev + 1 : 1;
        } else {
            // CS pertama kali
            $nextRev = 0;
        }

        $approvalCtl = app(ApprovalController::class);

        // 4) Pastikan line approval tersedia
        $approvalCtl->loadLines($doctype, $cpnyId, $deptId);

        \DB::connection('pgsql')->beginTransaction();
        try {
            /**
             * 5) Ambil sumber (header + detail) HANYA bila doc jenis SPPB/J/K/T.
             *    Untuk CS revisi dari PO (doc = 'PO', dll) → tidak usah ambil source SPPB/SPPJ/SPPK/SPPT.
             */
            $srcHeader = null;
            $srcDetails = collect();
            $srcLineKey = null;
            $srcIndex = [];
            $reuseIndex = [];
            $prevLocIndex = [];

            $allowedDocs = ['SPPB', 'SPPJ', 'SPPK', 'SPPT'];

            if (in_array($doc, $allowedDocs, true)) {
                $srcIdPlain = $srcId;

                // kalau src_id dari FE adalah hashids (eid), decode ke integer id
                if (!is_numeric($srcIdPlain)) {
                    $decoded = Hashids::decode((string) $srcIdPlain);
                    $srcIdPlain = $decoded[0] ?? null;
                }

                if (!$srcIdPlain) {
                    throw new \Exception("Invalid src_id (cannot decode/find id) for doc={$doc}");
                }

                [$srcHeader, $srcDetails, $srcLineKey, $srcIndex] = $this->buildSourceForDoc($doc, $srcId);

                // Index detail untuk lookup by key
                foreach ($srcDetails as $sd) {
                    $key = strtoupper(trim($sd->inventoryid ?? '')).'|'.
                        strtoupper(trim($sd->uom ?? '')).'|'.
                        strtoupper(trim($sd->inventory_descr ?? ''));
                    $srcIndex[$key] = $sd;
                }
            } else {
                // Kalau BUKAN revisi dan doc bukan SPPB/J/K/T → tolak
                if (empty($prev_csid)) {
                    throw new \Exception("Invalid doc type for new CS (doc={$doc})");
                }
                // Jika revisi (prev_csid ada), aman → kita hanya gunakan payload + update ke TrPOReuse
            }

            if (!empty($prev_csid)) {
                $reuseDetails = TrPOReuse::on('pgsql')
                    ->where('csid', $prev_csid)
                    ->get();

                foreach ($reuseDetails as $rd) {
                    $key = strtoupper(trim($rd->inventoryid ?? '')).'|'.
                        strtoupper(trim($rd->uom ?? '')).'|'.
                        strtoupper(trim($rd->inventory_descr ?? ''));
                    $reuseIndex[$key] = $rd;
                }
                $prevDetails2 = TrCSdetail::on('pgsql')
                    ->where('csid', $prev_csid)
                    ->get();

                foreach ($prevDetails2 as $pd) {
                    $key = strtoupper(trim($pd->inventoryid ?? '')).'|'.
                        strtoupper(trim($pd->uom ?? '')).'|'.
                        strtoupper(trim($pd->inventory_descr ?? ''));
                    $prevLocIndex[$key] = $pd; // simpan row prev utk ambil location
                }
            }

            $auto = $this->nextAutonbr(
                $doctype,
                $year,
                $month,
                $username,
                'CANVASSSHEET',
            );
            $urutan = (int) $auto['next'];

            $tglbln = substr((string) $year, 2).$month;   // YYMM
            $csid = $doctype.$tglbln.sprintf('%04d', $urutan);

            // 7) HEADER TrCS
            $cs = new TrCS();
            $cs->setConnection('pgsql');

            $cs->csid = $csid;
            $cs->csdate = $dt->toDateString();
            $cs->cpny_id = $cpnyId;
            $cs->sppbjktid = $sppbjktid;
            $cs->bqid = $bqid ?: ($srcHeader->bqid ?? null);
            $cs->woid = $woid ?: ($srcHeader->woid ?? null);
            $cs->spbid = $spbid ?: ($srcHeader->spbid ?? null);
            $cs->keperluan = $keperluan ?: ($srcHeader->keperluan ?? null);
            $cs->bqtype = $bqtype ?: ($srcHeader->bqtype ?? null);
            $cs->department_id = $deptId ?: ($srcHeader->department_id ?? null);
            $cs->user_peminta = $userPeminta ?: null;
            $cs->csnote = $csnote ?: null;
            $cs->assigndate = $assigndate ?: null;
            $cs->prev_csid = $prev_csid ?: null;
            $cs->rev_csid = $nextRev;
            $cs->status = 'H';        // sementara draft, nanti langsung di-set 'P'
            $cs->created_by = $username;

            $csTable = $cs->getTable();

            $safeSet($cs, $csTable, 'budget_perpost', $budgetPerpost ?? null);
            $safeSet($cs, $csTable, 'woid', $woid ?? null);
            $safeSet($cs, $csTable, 'spbid', $spbid ?? null);

            for ($slot = 1; $slot <= 6; ++$slot) {
                $v = $vendors[$slot - 1] ?? null;

                $vendorNote = $v['vendornote'] ?? null;
                if ($vendorNote !== null) {
                    $vendorNote = trim((string) $vendorNote);
                    if ($vendorNote === '') {
                        $vendorNote = null;
                    }
                    if ($vendorNote !== null) {
                        $vendorNote = mb_substr($vendorNote, 0, 500);
                    }
                }

                $safeSet($cs, $csTable, "vendorid{$slot}", $v['vendorid'] ?? null);
                $safeSet($cs, $csTable, "vendorname{$slot}", $v['vendorname'] ?? null);
                $safeSet($cs, $csTable, "vendoralamat{$slot}", $v['vendoralamat'] ?? null);
                $safeSet($cs, $csTable, "vendortelp{$slot}", $v['vendortelp'] ?? null);
                $safeSet($cs, $csTable, "vendorcp{$slot}", $v['vendorcp'] ?? null);
                $safeSet($cs, $csTable, "vendortop{$slot}", $v['vendortop'] ?? null);
                $safeSet($cs, $csTable, "vendornote{$slot}", $vendorNote);

                $safeSet($cs, $csTable, "totalvendor{$slot}", $round2($v['total'] ?? 0));
                $safeSet($cs, $csTable, "taxcodevendor{$slot}", $v['taxcode'] ?? null);
                $safeSet($cs, $csTable, "ppnvendor{$slot}", $round2($v['ppn'] ?? 0));
                $safeSet($cs, $csTable, "pphvendor{$slot}", $round2($v['pph'] ?? 0));
                $safeSet($cs, $csTable, "taxvendor{$slot}", $round2($v['tax'] ?? 0));
                $safeSet($cs, $csTable, "grandtotalvendor{$slot}", $round2($v['grand'] ?? 0));

                $safeSet($cs, $csTable, "totalselectedvendor{$slot}", $round2($v['selected_total'] ?? 0));
                $safeSet($cs, $csTable, "taxselectedvendor{$slot}", $round2($v['selected_tax'] ?? 0));
                $safeSet($cs, $csTable, "grandtotalselectedvendor{$slot}", $round2($v['selected_grand'] ?? 0));
            }

            $cs->save();

            // 8) DETAIL TrCSdetail + akumulasi header vendor selected
            $lineNo = 0;
            $docSelectedGrand = 0.0;
            // $selectedByVendor = [];
            // for ($i = 1; $i <= 6; $i++) {
            //     $selectedByVendor[$i] = ['total' => 0.0, 'tax' => 0.0, 'grand' => 0.0];
            // }

            foreach ($details as $d) {
                ++$lineNo;

                $matchKey = strtoupper(trim($d['inventoryid'] ?? '')).'|'.
                            strtoupper(trim($d['uom'] ?? '')).'|'.
                            strtoupper(trim($d['inventory_descr'] ?? ''));

                // $src = $srcIndex[$matchKey] ?? ($srcDetails[$lineNo - 1] ?? null);
                // $srcRefNo = $src ? ($src->{$srcLineKey} ?? null) : null;
                $src = $srcIndex[$matchKey] ?? ($srcDetails[$lineNo - 1] ?? null);
                if (!$src && !empty($prev_csid)) {
                    $src = $reuseIndex[$matchKey] ?? null;
                }

                if ($src) {
                    if (!empty($srcLineKey) && isset($src->{$srcLineKey})) {
                        $srcRefNo = $src->{$srcLineKey};
                    } elseif (isset($src->sppbjkt_no)) {
                        // fallback untuk revisi (ambil sppbjkt_no langsung dari TrPOReuse)
                        $srcRefNo = $src->sppbjkt_no;
                    } else {
                        $srcRefNo = null;
                    }
                } else {
                    $srcRefNo = null;
                }

                $prevLoc = (!empty($prev_csid) && isset($prevLocIndex[$matchKey])) ? $prevLocIndex[$matchKey] : null;

                $det = new TrCSdetail();
                $det->setConnection('pgsql');

                $det->csid = $csid;
                $det->sppbjktid = $sppbjktid;
                $det->cs_no = $lineNo;
                $det->sppbjkt_no = $srcRefNo;

                // inventory fields (payload > sumber)
                $det->inventory_type = $d['inventory_type'] ?? ($src->inventory_type ?? null);
                $det->inventoryid = $d['inventoryid'] ?? ($src->inventoryid ?? null);
                $det->inventory_descr = $d['inventory_descr'] ?? ($src->inventory_descr ?? null);

                // >>> tambah ini supaya tidak null <<<
                $det->inventory_sub_type = $d['inventory_sub_type'] ?? ($src->inventory_sub_type ?? null);
                $det->inventory_category = $d['inventory_category'] ?? ($src->inventory_category ?? null);

                $det->qty = $round2($d['qty'] ?? ($src->qty ?? 0));
                $det->uom = $d['uom'] ?? ($src->uom ?? null);
                $det->siteid = $d['siteid'] ?? ($src->siteid ?? null);

                // konversi dari sumber (jika ada)
                $det->type_multiplier = $src->type_multiplier ?? null;
                $det->base_multiplier = isset($src->base_multiplier) ? $round2($src->base_multiplier) : null;
                $det->base_qty = isset($src->base_qty) ? $round2($src->base_qty) : null;
                $det->base_uom = $src->base_uom ?? null;

                // harga terakhir & note
                // $det->inventory_last_price = isset($d['inventory_last_price']) ? $round2($d['inventory_last_price'])
                //                             : (isset($src->inventory_last_price) ? $round2($src->inventory_last_price) : 0);
                // $det->inventory_last_price = $lastPriceMap[$det->inventoryid] ?? 0;
                $det->inventory_last_price = isset($d['inventory_last_price']) ? $round2($d['inventory_last_price'])
                                                : (isset($src->inventory_last_price) ? $round2($src->inventory_last_price) : 0);
                $det->csnote_detail = $d['csnote_detail'] ?? ($src->note ?? null);

                // lokasi & budgeting
                // $det->location_id               = $src->location_id               ?? null;
                // $det->sub_location_id           = $src->sub_location_id           ?? null;
                $det->location_id = $src->location_id ?? ($prevLoc->location_id ?? null);
                $det->sub_location_id = $src->sub_location_id ?? ($prevLoc->sub_location_id ?? null);
                $det->budget_perpost = $src->budget_perpost ?? null;
                $det->budget_cpny_id = $cpnyId; // tetap perusahaan CS
                $det->budget_business_unit_id = $src->budget_business_unit_id ?? null;
                $det->budget_department_fin_id = $src->budget_department_fin_id ?? null;
                $det->budget_account_id = $src->budget_account_id ?? null;
                $det->budget_activity_id = $src->budget_activity_id ?? null;
                $det->budget_activity_descr = $src->budget_activity_descr ?? null;

                // harga vendor + akumulasi selected
                // $selectedGrandThisRow = 0.0;

                for ($i = 0; $i < min(count($d['vendor'] ?? []), 6); ++$i) {
                    $slot = $i + 1;
                    $vrow = $d['vendor'][$i];
                    $vid = $vrow['vendorid'] ?? null;
                    $price = $round2($vrow['price'] ?? 0);
                    $total = $round2($vrow['total'] ?? 0);
                    $ppn = $round2($vrow['ppn'] ?? 0);
                    $pph = $round2($vrow['pph'] ?? 0);
                    $tax = $round2($vrow['tax'] ?? ($ppn + $pph));
                    $grand = $round2($vrow['grand'] ?? ($total + $tax));
                    $sel = !empty($vrow['selected']);

                    $det->{"vendorid{$slot}"} = $vid;
                    $det->{"vendorprice{$slot}"} = $price;
                    $det->{"vendortotalprice{$slot}"} = $total;
                    $det->{"vendor{$slot}selected"} = (bool) $sel;

                    // if ($sel) {
                    //     $selectedGrandThisRow = $grand;
                    //     $selectedByVendor[$slot]['total'] += $total;
                    //     $selectedByVendor[$slot]['tax']   += $tax;
                    //     $selectedByVendor[$slot]['grand'] += $grand;
                    // }
                }

                // $docSelectedGrand += $selectedGrandThisRow;

                $det->status = 'H';   // sementara draft, nanti di-set 'P'
                $det->created_by = $username;
                $det->save();
            }

            // 9) Update kolom selected vendor di HEADER
            // for ($slot = 1; $slot <= 6; $slot++) {
            //     $safeSet($cs, $csTable, "totalselectedvendor{$slot}",      $round2($selectedByVendor[$slot]['total']));
            //     $safeSet($cs, $csTable, "taxselectedvendor{$slot}",        $round2($selectedByVendor[$slot]['tax']));
            //     $safeSet($cs, $csTable, "grandtotalselectedvendor{$slot}", $round2($selectedByVendor[$slot]['grand']));
            // }
            $cs->save();

            // 10) Attachments (kalau ada)
            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr' => $cs->csid,
                    'doctype' => $doctype,
                    'cpnyid' => $cpnyId,
                    'departementid' => $deptId,
                    'base_folder' => 'att-purchasing-app/'.strtolower($doctype),
                    'created_by' => $username,
                ];
                $files = (array) $request->file('attachments');

                try {
                    $uploader = app(TrAttachmentController::class);
                    $uploader->uploadInternal($meta, $files);
                } catch (\Throwable $e) {
                    \DB::connection('pgsql')->rollBack();

                    return response()->json([
                        'ok' => false,
                        'message' => 'Gagal upload attachment: '.$e->getMessage(),
                    ], 500);
                }
            }

            // 11) ==== SELALU SUBMIT di sini (status = 'P') ====

            if (empty($prev_csid)) {
                // (a) Validasi submit server-side
                $this->validateSubmitServerSide($details);

                // (b) Update ordered/openordered pada dokumen sumber (SPPB/SPPJ/SPPK/SPPT)
                if (in_array($doc, $allowedDocs, true)) {
                    $this->updateOrderedOnSource($details, $srcHeader, $srcDetails, $srcIndex, $cpnyId);
                }
                if ($cs->bqtype !== 'Kontrak') {
                    // (c) Reserve budget via SP (Submit)
                    $this->reserveBudget('CS', $cs->csid,$cpnyId, 'Submit', $username);
                }
            } else {
                // Update ordered/openordered ke TrPOReuse + header PO
                $this->updateOrderedOnPOReuse($details, $prev_csid, $cpnyId);

                if ($cs->bqtype !== 'Kontrak') {
                    // Reserve budget via SP (Submit)
                    $this->reserveBudget('CS', $cs->csid,$cpnyId, 'Submit', $username);
                }
            }

            // (d) Set status header & detail = Pending, set submitdate
            $cs->status = 'P';
            if (Schema::connection('pgsql')->hasColumn($csTable, 'submitdate')) {
                $cs->submitdate = $dt;
            }
            if (Schema::connection('pgsql')->hasColumn($csTable, 'updated_by')) {
                $cs->updated_by = $username;
            }
            $cs->save();

            TrCSdetail::on('pgsql')->where('csid', $csid)->update(['status' => 'P']);

            // (e) Generate TrApproval + email approver pertama
            $ctx = [
                'ignore_nominal' => false,
                'grand_total' => (float) $docSelectedGrand,
            ];

            [$firstApprovalUsernames, $linesCount] = $approvalCtl->generateForDocument(
                $cs->csid,
                $doctype,
                $cpnyId,
                $deptId,
                $username,
                $ctx,
                $dt
            );

            if ($firstApprovalUsernames) {
                $cs->completed_by = $firstApprovalUsernames;
                $cs->completed_at = $dt;
                $cs->save();
            }

            $this->applyFastApproveForCS($cs->csid, $username, $dt);

            $eid = Hashids::encode($cs->id);
            $approvalCtl->notifyFirstApprover(
                $cs->csid,
                $doctype,
                $cs->status,  // 'P'
                'CS',
                url('/showcs/'.$eid),
                [
                    'info' => $csnote ?: ($srcHeader->keperluan ?? ''),
                    'createdby' => $cs->created_by,
                    'date' => $dt->toDateTimeString(),
                ]
            );

            \DB::connection('pgsql')->commit();

            return response()->json([
                'ok' => true,
                'message' => 'CS berhasil dibuat & diajukan',
                'csid' => $cs->csid,
                'grand_total' => $round2($docSelectedGrand),
                'status' => $cs->status,
                'submitdate' => optional($cs->submitdate)->toDateTimeString(),
            ]);
        } catch (\Throwable $e) {
            \DB::connection('pgsql')->rollBack();
            report($e);

            return response()->json([
                'ok' => false,
                'message' => 'Failed to create CS: '.(config('app.debug') ? $e->getMessage() : 'Terjadi kesalahan'),
            ], 500);
        }
    }

    public function saveCS(Request $request)
    {
        //  dd($request->all());
        // ==== Ambil input dasar dari form (hidden + payload JSON) ====
        $doc = strtoupper($request->input('doc'));          // SPPB|SPPJ|SPPK|SPPT|PO (revisi)
        $srcId = $request->input('src_id');                   // id sumber doc
        $sppbjktid = $request->input('sppbjktid');                // docno ditaruh ke sini
        $cpnyId = $request->input('cpny_id');
        $deptId = $request->input('department_id');
        $bqid = $request->input('bqid');
        $userPeminta = $request->input('user_peminta');
        $csnote = $request->input('csnote');
        $assigndate = $request->input('assigndate');
        $prev_csid = $request->input('prev_csid');
        $spbid = $request->input('spbid');
        $woid = $request->input('woid');
        $keperluan = $request->input('keperluan');
        $bqtype = $request->input('bqtype');
        $budgetPerpost = $request->input('budget_perpost');

        // Dari JS: vendors[] + details[]
        $vendors = json_decode($request->input('vendors', '[]'), true) ?: [];
        $details = json_decode($request->input('details', '[]'), true) ?: [];

        $doctype = 'CS';
        $user = $request->user();
        $username = $user->username ?? 'system';
        $fullname = $user->name ?? 'system';

        $dt = Carbon::now();
        $year = (int) $dt->year;
        $month = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
        $datestamp = $dt->toDateTimeString();

        $round2 = fn ($n) => round((float) $n, 2);
        $safeSet = function ($model, string $table, string $column, $value) {
            if (Schema::connection('pgsql')->hasColumn($table, $column)) {
                $model->{$column} = $value;
            }
        };

        // Hitung rev_csid
        if ($prev_csid) {
            // ada CS sebelumnya → revisi dari CS awal (prev_csid = CS A)
            $lastRev = TrCS::where('prev_csid', $prev_csid)->max('rev_csid');
            $nextRev = $lastRev ? $lastRev + 1 : 1;
        } else {
            // CS baru pertama kali, belum revisi
            $nextRev = 0;
        }

        // ==== 1) Approval line check (doctype CS) ====

        DB::connection('pgsql')->beginTransaction();
        try {
            /**
             * 2) Ambil header & detail sumber HANYA bila doc jenis SPPB/J/K/T.
             *    Untuk CS revisi dari PO (doc = 'PO', dll) → tidak usah ambil source SPPB/SPPJ/SPPK/SPPT.
             */
            $srcHeader = null;
            $srcDetails = collect();
            $srcLineKey = null;   // nama kolom nomor urut detail di sumber
            $srcIndex = [];
            $reuseIndex = [];

            $allowedDocs = ['SPPB', 'SPPJ', 'SPPK', 'SPPT'];

            if (in_array($doc, $allowedDocs, true)) {
                switch ($doc) {
                    case 'SPPB':
                        $srcHeader = TrSPPB::with(['requestType', 'creator', 'purchaser'])->findOrFail($srcId);
                        $srcDetails = TrSPPBdetail::where('sppbid', $srcHeader->sppbid)->get();
                        $srcLineKey = 'sppb_no';
                        break;
                    case 'SPPJ':
                        $srcHeader = TrSPPJ::with(['requestType', 'creator', 'purchaser'])->findOrFail($srcId);
                        $srcDetails = TrSPPJdetail::where('sppjid', $srcHeader->sppjid)->get();
                        $srcLineKey = 'sppj_no';
                        break;
                    case 'SPPK':
                        $srcHeader = TrSPPK::with(['requestType', 'creator', 'purchaser'])->findOrFail($srcId);
                        $srcDetails = TrSPPKdetail::where('sppkid', $srcHeader->sppkid)->get();
                        $srcLineKey = 'sppk_no';
                        break;
                    case 'SPPT':
                        $srcHeader = TrSPPT::with(['requestType', 'creator', 'purchaser'])->findOrFail($srcId);
                        $srcDetails = TrSPPTdetail::where('spptid', $srcHeader->spptid)->get();
                        $srcLineKey = 'sppt_no';
                        break;
                }

                // index-kan detail sumber
                foreach ($srcDetails as $sd) {
                    $key = strtoupper(trim($sd->inventoryid ?? '')).'|'.
                        strtoupper(trim($sd->uom ?? '')).'|'.
                        strtoupper(trim($sd->inventory_descr ?? ''));
                    $srcIndex[$key] = $sd;
                }
            } else {
                // Kalau BUKAN revisi dan doc bukan SPPB/J/K/T → tolak
                // if (empty($prev_csid)) {
                //     abort(422, 'Invalid doc type');
                // }
                // Jika revisi (prev_csid ada), aman → kita hanya gunakan payload + TrPOReuse
            }

            $prevDetIndex = [];
            $prevLocIndex = [];

            if (!empty($prev_csid)) {
                $prevDetails = TrPOReuse::on('pgsql')
                    ->where('csid', $prev_csid)
                    ->get();

                foreach ($prevDetails as $pd) {
                    $key = strtoupper(trim($pd->inventoryid ?? '')).'|'.
                        strtoupper(trim($pd->uom ?? '')).'|'.
                        strtoupper(trim($pd->inventory_descr ?? ''));
                    $prevDetIndex[$key] = $pd;
                }

                $prevDetails2 = TrCSdetail::on('pgsql')
                    ->where('csid', $prev_csid)
                    ->get();

                foreach ($prevDetails2 as $pd) {
                    $key = strtoupper(trim($pd->inventoryid ?? '')).'|'.
                        strtoupper(trim($pd->uom ?? '')).'|'.
                        strtoupper(trim($pd->inventory_descr ?? ''));
                    $prevLocIndex[$key] = $pd; // simpan row prev utk ambil location
                }
            }

            // ==== 3) Generate autonbr CS (lock for update) ====
            $auto = $this->nextAutonbr(
                $doctype,
                $year,
                $month,
                $username,
                'CANVASSSHEET',
            );
            $urutan = (int) $auto['next'];

            $tglbln = substr((string) $year, 2).$month;   // YYMM
            $csid = $doctype.$tglbln.sprintf('%04d', $urutan);

            $prevCS = null;
            if (!empty($prev_csid)) {
                $prevCS = TrCS::on('pgsql')->where('csid', $prev_csid)->first();
            }

            // ==== 4) Simpan header TrCS (lengkapi dari header sumber jika ada) ====
            $cs = new TrCS();
            $cs->setConnection('pgsql');
            $cs->csid = $csid;
            $cs->csdate = $dt->toDateString();
            $cs->cpny_id = $cpnyId;
            $cs->sppbjktid = $sppbjktid;

            $cs->keperluan = $keperluan ?: ($srcHeader->keperluan ?? null);
            $cs->bqtype = $bqtype ?: ($srcHeader->bqtype ?? null);
            $cs->department_id = $deptId ?: ($srcHeader->department_id ?? null);
            $cs->budget_perpost = $budgetPerpost ?? null;
            // $cs->user_peminta  = $userPeminta ?: (optional($srcHeader->creator)->name ?? null);
            $cs->user_peminta = $userPeminta ?: null;
            $cs->csnote = $csnote ?: null;
            $cs->assigndate = $assigndate ?: null;
            $cs->prev_csid = $prev_csid ?: null;
            $cs->rev_csid = $nextRev;
            // $cs->budget_perpost = $srcHeader->budget_perpost ?? ($prevCS->budget_perpost ?? null);
            $cs->woid = $woid ?: ($srcHeader->woid ?? ($prevCS->woid ?? null));
            $cs->spbid = $spbid ?: ($srcHeader->spbid ?? ($prevCS->spbid ?? null));
            $cs->bqid = $bqid ?: ($srcHeader->bqid ?? ($prevCS->bqid ?? null));

            $csTable = $cs->getTable();
            $safeSet($cs, $csTable, 'budget_perpost', $budgetPerpost ?? null);
            $safeSet($cs, $csTable, 'woid', $woid ?? null);
            $safeSet($cs, $csTable, 'spbid', $spbid ?? null);

            $cs->status = 'H';
            $cs->created_by = $username;

            // Map maksimal 6 vendor
            for ($i = 0; $i < min(count($vendors), 6); ++$i) {
                $idx = $i + 1;
                $v = $vendors[$i];

                // note vendor: trim + batasi panjang (mis 500)
                $vendorNote = $v['vendornote'] ?? null;
                if ($vendorNote !== null) {
                    $vendorNote = trim((string) $vendorNote);
                    if ($vendorNote === '') {
                        $vendorNote = null;
                    }
                    // batasi panjang agar aman (sesuaikan jika kolom kamu lebih kecil/besar)
                    if ($vendorNote !== null) {
                        $vendorNote = mb_substr($vendorNote, 0, 500);
                    }
                }

                $safeSet($cs, $csTable, "vendorid{$idx}", $v['vendorid'] ?? null);
                $safeSet($cs, $csTable, "vendorname{$idx}", $v['vendorname'] ?? null);
                $safeSet($cs, $csTable, "vendoralamat{$idx}", $v['vendoralamat'] ?? null);
                $safeSet($cs, $csTable, "vendortelp{$idx}", $v['vendortelp'] ?? null);
                $safeSet($cs, $csTable, "vendorcp{$idx}", $v['vendorcp'] ?? null);
                $safeSet($cs, $csTable, "vendortop{$idx}", $v['vendortop'] ?? null);

                // ✅ Vendor note
                $safeSet($cs, $csTable, "vendornote{$idx}", $vendorNote);

                $safeSet($cs, $csTable, "totalvendor{$idx}", $round2($v['total'] ?? 0));
                $safeSet($cs, $csTable, "taxcodevendor{$idx}", $v['taxcode'] ?? null);
                $safeSet($cs, $csTable, "ppnvendor{$idx}", $round2($v['ppn'] ?? 0));
                $safeSet($cs, $csTable, "pphvendor{$idx}", $round2($v['pph'] ?? 0));
                $safeSet($cs, $csTable, "taxvendor{$idx}", $round2($v['tax'] ?? 0));
                $safeSet($cs, $csTable, "grandtotalvendor{$idx}", $round2($v['grand'] ?? 0));
                $safeSet($cs, $csTable, "totalselectedvendor{$idx}", $round2($v['selected_total'] ?? 0));
                $safeSet($cs, $csTable, "taxselectedvendor{$idx}", $round2($v['selected_tax'] ?? 0));
                $safeSet($cs, $csTable, "grandtotalselectedvendor{$idx}", $round2($v['selected_grand'] ?? 0));
            }

            $cs->save();

            // ==== 5) Simpan detail TrCSdetail (lengkapi dari sumber / TrPOReuse) ====
            $lineNo = 0;
            foreach ($details as $d) {
                ++$lineNo;

                $matchKey = strtoupper(trim($d['inventoryid'] ?? '')).'|'.
                            strtoupper(trim($d['uom'] ?? '')).'|'.
                            strtoupper(trim($d['inventory_descr'] ?? ''));

                // Utama: ambil dari sumber SPPB/J/K/T bila ada
                $src = $srcIndex[$matchKey] ?? ($srcDetails[$lineNo - 1] ?? null);

                // Jika CS revisi & tidak ketemu di sumber dokumen awal → fallback ke TrPOReuse
                if (!$src && !empty($prev_csid)) {
                    $src = $reuseIndex[$matchKey] ?? null;
                }

                // tentukan nomor ref detail
                if ($src) {
                    if (!empty($srcLineKey) && isset($src->{$srcLineKey})) {
                        $srcRefNo = $src->{$srcLineKey};
                    } elseif (isset($src->sppbjkt_no)) {
                        // untuk revisi PO (TrPOReuse)
                        $srcRefNo = $src->sppbjkt_no;
                    } else {
                        $srcRefNo = null;
                    }
                } else {
                    $srcRefNo = null;
                }

                $prevDet = (!empty($prev_csid) && isset($prevDetIndex[$matchKey])) ? $prevDetIndex[$matchKey] : null;
                $prevLoc = (!empty($prev_csid) && isset($prevLocIndex[$matchKey])) ? $prevLocIndex[$matchKey] : null;

                $det = new TrCSdetail();
                $det->setConnection('pgsql');
                $det->csid = $csid;
                $det->sppbjktid = $sppbjktid;
                $det->cs_no = $lineNo;
                $det->sppbjkt_no = $srcRefNo ?? ($prevDet->sppbjkt_no ?? null);

                // inventory fields (payload > sumber/TrPOReuse)
                $det->inventoryid = $d['inventoryid'] ?? ($src->inventoryid ?? null);
                $det->inventory_descr = $d['inventory_descr'] ?? ($src->inventory_descr ?? null);
                $det->inventory_type = $d['inventory_type'] ?? ($src->inventory_type ?? ($prevDet->inventory_type ?? null));
                $det->inventory_sub_type = $d['inventory_sub_type'] ?? ($src->inventory_sub_type ?? ($prevDet->inventory_sub_type ?? null));
                $det->inventory_category = $d['inventory_category'] ?? ($src->inventory_category ?? ($prevDet->inventory_category ?? null));

                $det->qty = $round2($d['qty'] ?? ($src->qty ?? 0));
                $det->uom = $d['uom'] ?? ($src->uom ?? null);
                // $det->siteid               = $d['siteid']             ?? ($src->siteid ?? null);

                // // konversi UOM dari sumber
                $det->siteid = $d['siteid'] ?? ($src->siteid ?? ($prevDet->siteid ?? null));
                $det->type_multiplier = $src->type_multiplier ?? ($prevDet->type_multiplier ?? null);
                $det->base_multiplier = isset($src->base_multiplier) ? $round2($src->base_multiplier)
                                    : (isset($prevDet->base_multiplier) ? $round2($prevDet->base_multiplier) : null);
                $det->base_qty = isset($src->base_qty) ? $round2($src->base_qty)
                                    : (isset($prevDet->base_qty) ? $round2($prevDet->base_qty) : null);
                $det->base_uom = $src->base_uom ?? ($prevDet->base_uom ?? null);

                // harga terakhir & note
                $det->inventory_last_price = isset($d['inventory_last_price']) ? $round2($d['inventory_last_price'])
                                                : (isset($src->inventory_last_price) ? $round2($src->inventory_last_price) : 0);
                $det->csnote_detail = $d['csnote_detail'] ?? ($src->note ?? null);

                // lokasi & budgeting
                $det->location_id = $src->location_id ?? ($prevLoc->location_id ?? null);
                $det->sub_location_id = $src->sub_location_id ?? ($prevLoc->sub_location_id ?? null);
                $det->budget_cpny_id = $cpnyId; // tetap perusahaan CS
                $det->budget_perpost = $src->budget_perpost ?? ($prevDet->budget_perpost ?? null);
                $det->budget_business_unit_id = $src->budget_business_unit_id ?? ($prevDet->budget_business_unit_id ?? null);
                $det->budget_department_fin_id = $src->budget_department_fin_id ?? ($prevDet->budget_department_fin_id ?? null);
                $det->budget_account_id = $src->budget_account_id ?? ($prevDet->budget_account_id ?? null);
                $det->budget_activity_id = $src->budget_activity_id ?? ($prevDet->budget_activity_id ?? null);
                $det->budget_activity_descr = $src->budget_activity_descr ?? ($prevDet->budget_activity_descr ?? null);

                // Map harga per vendor (maks 6)
                for ($i = 0; $i < min(count($d['vendor'] ?? []), 6); ++$i) {
                    $idx = $i + 1;
                    $vrow = $d['vendor'][$i];
                    $vid = $vrow['vendorid'] ?? null;
                    $price = $round2($vrow['price'] ?? 0);
                    $total = $round2($vrow['total'] ?? 0);
                    $sel = !empty($vrow['selected']);

                    $det->{"vendorid{$idx}"} = $vid;
                    $det->{"vendorprice{$idx}"} = $price;
                    $det->{"vendortotalprice{$idx}"} = $total;
                    $det->{"vendor{$idx}selected"} = (bool) $sel;
                }

                $det->status = 'H';
                $det->created_by = $username;
                $det->save();
            }

            // ==== 6) Attachments (jika ada) ====
            $uploadResult = null;
            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr' => $csid,
                    'doctype' => $doctype,
                    'cpnyid' => $cpnyId,
                    'departementid' => $deptId,
                    'base_folder' => 'att-purchasing-app/'.strtolower($doctype),
                    'created_by' => $user->username,
                ];

                $files = (array) $request->file('attachments');

                try {
                    $uploader = app(TrAttachmentController::class);
                    $uploadResult = $uploader->uploadInternal($meta, $files);
                } catch (\Throwable $e) {
                    DB::connection('pgsql')->rollBack();

                    return response()->json([
                        'message' => 'Failed to create CS',
                        'error' => 'Gagal upload attachment: '.$e->getMessage(),
                    ], 500);
                }
            }

            DB::connection('pgsql')->commit();

            return response()->json([
                'message' => 'CS created successfully',
                'csid' => $csid,
                'attachments' => $uploadResult,
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql')->rollBack();
            report($e);

            return response()->json([
                'message' => 'Failed to create CS',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function editCS(string $eid)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $ids = Hashids::decode($eid);
        abort_if(empty($ids), 404);
        $id = $ids[0];

        $cs = TrCS::findOrFail($id);

        $docno = (string) $cs->sppbjktid;
        $prefix2 = strtoupper(substr($docno, 0, 2));
        $map = ['PB' => 'SPPB', 'PJ' => 'SPPJ', 'PK' => 'SPPK', 'PT' => 'SPPT'];
        $doc = $map[$prefix2] ?? 'SPPB';

        // header dokumen sumber (untuk tampilan readonly di header)
        $header = null;
        switch ($doc) {
            case 'SPPB': $header = TrSPPB::with(['creator', 'purchaser'])->where('sppbid', $docno)->first();
                $top_type = 'PO';
                break;
            case 'SPPJ': $header = TrSPPJ::with(['creator', 'purchaser'])->where('sppjid', $docno)->first();
                $top_type = 'SPK';
                break;
            case 'SPPK': $header = TrSPPK::with(['creator', 'purchaser'])->where('sppkid', $docno)->first();
                $top_type = 'SPK';
                break;
            case 'SPPT': $header = TrSPPT::with(['creator', 'purchaser'])->where('spptid', $docno)->first();
                $top_type = 'SPK';
                break;
        }

        // Detail baris CS
        $items = TrCSdetail::where('csid', $cs->csid)
            ->orderBy(DB::raw('COALESCE(sppbjkt_no, cs_no)'))
            ->get();

        // === Bentuk vendor summary dari kolom TrCS vendor1..6 ===
        // kita pakai vendoridX sebagai "kode vendor" (key utama),
        // dan jadikan juga sebagai "id" kolom agar konsisten di atribut data-vendor-id.
        $vendorsUsed = [];
        for ($i = 1; $i <= 6; ++$i) {
            $vid = $cs->{"vendorid{$i}"} ?? null; // KODE vendor (string)
            if (!$vid) {
                continue;
            }

            $taxcode = trim((string) ($cs->{"taxcodevendor{$i}"} ?? ''));
            $parts = array_values(array_filter(array_map('trim', explode('+', $taxcode))));

            $ppnId = null;
            $pphId = null;

            foreach ($parts as $part) {
                if (stripos($part, 'PPN') === 0) {
                    $ppnId = $part;
                } elseif (stripos($part, 'PPH') === 0) {
                    $pphId = $part;
                } elseif (strtoupper($part) === 'NONTAX') {
                    $ppnId = 'NONTAX';
                }
            }

            $vendorsUsed[] = [
                'id' => $vid,
                'vendor_id' => $vid,
                'vendor_name' => $cs->{"vendorname{$i}"} ?? '',
                'vendor_addr1' => $cs->{"vendoralamat{$i}"} ?? '',
                'phone_number' => $cs->{"vendortelp{$i}"} ?? '',
                'contact_person' => $cs->{"vendorcp{$i}"} ?? '',
                'top' => $cs->{"vendortop{$i}"} ?? '30D',
                'vendornote' => $cs->{"vendornote{$i}"} ?? '',

                'taxcode' => $taxcode,
                'ppn' => (float) ($cs->{"ppnvendor{$i}"} ?? 0),
                'pph' => (float) ($cs->{"pphvendor{$i}"} ?? 0),
                'total' => (float) ($cs->{"totalvendor{$i}"} ?? 0),
                'tax' => (float) ($cs->{"taxvendor{$i}"} ?? 0),
                'grand' => (float) ($cs->{"grandtotalvendor{$i}"} ?? 0),
                'sel_total' => (float) ($cs->{"totalselectedvendor{$i}"} ?? 0),
                'sel_tax' => (float) ($cs->{"taxselectedvendor{$i}"} ?? 0),
                'sel_grand' => (float) ($cs->{"grandtotalselectedvendor{$i}"} ?? 0),

                'ppn_id' => $ppnId,
                'pph_id' => $pphId,
            ];

            // $vendorsUsed[] = [
            //     'id' => $vid, // pakai kode sebagai id kolom
            //     'vendor_id' => $vid, // kode (untuk dicocokkan di detail)
            //     'vendor_name' => $cs->{"vendorname{$i}"} ?? '',
            //     'vendor_addr1' => $cs->{"vendoralamat{$i}"} ?? '',
            //     'phone_number' => $cs->{"vendortelp{$i}"} ?? '',
            //     'contact_person' => $cs->{"vendorcp{$i}"} ?? '',
            //     'top' => $cs->{"vendortop{$i}"} ?? '30D',
            //     'vendornote' => $cs->{"vendornote{$i}"} ?? '',

            //     // pajak & ringkasan
            //     'taxcode' => $cs->{"taxcodevendor{$i}"} ?? '',
            //     'ppn' => (float) ($cs->{"ppnvendor{$i}"} ?? 11),
            //     'pph' => (float) ($cs->{"pphvendor{$i}"} ?? 0),
            //     'total' => (float) ($cs->{"totalvendor{$i}"} ?? 0),
            //     'tax' => (float) ($cs->{"taxvendor{$i}"} ?? 0),
            //     'grand' => (float) ($cs->{"grandtotalvendor{$i}"} ?? 0),
            //     'sel_total' => (float) ($cs->{"totalselectedvendor{$i}"} ?? 0),
            //     'sel_tax' => (float) ($cs->{"taxselectedvendor{$i}"} ?? 0),
            //     'sel_grand' => (float) ($cs->{"grandtotalselectedvendor{$i}"} ?? 0),
            //     // optional: jika kamu simpan tax id terpisah, isi di sini (sekarang tidak ada)
            //     'ppn_id' => null,
            //     'pph_id' => null,
            // ];
        }

        // === Matriks detail per baris-per vendor dari TrCSdetail ===
        // DETAIL_MATRIX[rowIndex][vendor_code] = ['price'=>..., 'total'=>..., 'selected'=>bool]
        $detailVendorMatrix = [];
        foreach ($items as $idx => $row) {
            $detailVendorMatrix[$idx] = [];
            for ($i = 1; $i <= 6; ++$i) {
                $code = $row->{"vendorid{$i}"} ?? null;  // KODE vendor
                if (!$code) {
                    continue;
                }

                $detailVendorMatrix[$idx][$code] = [
                    'price' => (float) ($row->{"vendorprice{$i}"} ?? 0),
                    'total' => (float) ($row->{"vendortotalprice{$i}"} ?? 0),
                    'selected' => (bool) ($row->{"vendor{$i}selected"} ?? false),
                ];
            }
        }

        // $attachment = Attachment::where('docid', $cs->sppbjktid)->where('status','A')->orderBy('created_at')->get();
        // $attachmentCS = Attachment::where('docid', $cs->csid)->where('status','A')->orderBy('created_at')->get();

        // --- helper: ambil daftar attachment TrAttachment + signed URL GCS ---
        $fetchGcsAttachments = function (string $refnbr) {
            $rows = TrAttachment::where('refnbr', $refnbr)
                ->where('status', 'A')
                ->orderBy('created_at', 'asc')   // sesuai permintaan: ASC
                ->get();

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

            return $rows->map(function ($r) use ($bucket) {
                $objectPath = rtrim($r->folder ?? '', '/').'/'.ltrim($r->filename ?? '', '/');
                $object = $bucket->object($objectPath);

                $signedUrl = null;
                try {
                    $signedUrl = $object->signedUrl(
                        new \DateTimeImmutable('+10 minutes'),
                        ['version' => 'v4']
                    );
                } catch (\Throwable $e) {
                    \Log::warning('Signed URL gagal', [
                        'path' => $objectPath,
                        'error' => $e->getMessage(),
                    ]);
                }

                return (object) [
                    'id' => $r->id,
                    'display_name' => $r->attachment_name ?? $r->filename,
                    'created_by' => $r->created_by,
                    'created_at' => $r->created_at,
                    'url' => $signedUrl,     // dipakai di view
                    'folder' => $r->folder,
                    'filename' => $r->filename,
                    'extention' => $r->extention,
                    'size' => $r->filesize,
                ];
            });
        };

        // --- ambil attachment sumber dokumen & CS (GCS) ---
        $attachment = $fetchGcsAttachments($cs->sppbjktid); // SPPB/J/K/T
        $attachmentCS = $fetchGcsAttachments($cs->csid);      // CS

        $eid = Hashids::encode($cs->id);

        $bq = null;
        $bq_eid = null;
        if (!empty($cs->bqid)) {
            $bq = TrBQCS::where('bqid', $cs->bqid)
                ->where('csid', $cs->csid)
                ->first();
            if ($bq) {
                $bq_eid = Hashids::encode($bq->id);
            }
        }

        // --- siapkan pembanding: total per vendor dari CS & BQ
        $csVendorTotals = [];  // [idx => ['vendorid','vendorname','total_cs']]
        $bqVendorTotals = [];  // [idx => ['grand_mat','grand_jsa','sum_bq']]

        for ($i = 1; $i <= 6; ++$i) {
            $vid = $cs->{"vendorid{$i}"} ?? null;
            $vnm = $cs->{"vendorname{$i}"} ?? null;

            // total dari CS (kolom totalvendor{i})
            $totalCS = (float) ($cs->{"totalvendor{$i}"} ?? 0);

            if ($vid || $vnm || $totalCS > 0) {
                $csVendorTotals[$i] = [
                    'vendorid' => $vid,
                    'vendorname' => $vnm,
                    'total_cs' => $totalCS,
                ];
            }

            // total dari BQ: grandtotalmaterialvendor{i} + grandtotaljasavendor{i}
            if ($bq) {
                $gmat = (float) ($bq->{"grandtotalmaterialvendor{$i}"} ?? 0);
                $gjsa = (float) ($bq->{"grandtotaljasavendor{$i}"} ?? 0);
                $bqVendorTotals[$i] = [
                    'grand_mat' => $gmat,
                    'grand_jsa' => $gjsa,
                    'sum_bq' => $gmat + $gjsa,
                ];
            }
        }

        // ambil inventoryid yang ada di items
        $invIds = collect($items)
            ->pluck('inventoryid')
            ->filter()
            ->unique()
            ->values()
            ->all();

        // map: inventoryid => latest unitcost
        $lastUnitcostMap = [];

        if (!empty($invIds)) {
            $rows = TrPoLastPrice::query()
                ->select('inventoryid', 'unitcost', 'podate', 'created_at') // unitcost wajib, lainnya hanya untuk orderBy
                ->whereIn('inventoryid', $invIds)
                ->whereNull('deleted_at')
                ->orderByDesc('podate')       // terbaru
                ->orderByDesc('created_at')   // tie breaker
                ->get();

            // ambil baris pertama (latest) untuk tiap inventoryid
            $lastUnitcostMap = $rows
                ->groupBy('inventoryid')
                ->map(fn ($g) => (float) ($g->first()->unitcost ?? 0))
                ->toArray();
        }

        // inject ke tiap item (biar gampang dipakai di blade)
        $items = collect($items)->map(function ($it) use ($lastUnitcostMap) {
            $invId = $it->inventoryid ?? null;
            $it->last_unitcost = $invId ? ($lastUnitcostMap[$invId] ?? 0) : 0;

            return $it;
        });

        $tops = MsTop::where('status', 'A')
            ->where('top_type', $top_type)
            ->orderByRaw('COALESCE(top_days, 9999), top_name')
            ->get(['topid', 'top_name', 'top_days', 'top_type']);

        $sourceShowUrl = null;
        switch ($doc) {
            case 'SPPB':
                $eid_doc = Hashids::encode($header->id);
                $sourceShowUrl = url('/showsppbs/'.$eid_doc);
                break;
            case 'SPPJ':
                $eid_doc = Hashids::encode($header->id);
                $sourceShowUrl = url('/showsppjs/'.$eid_doc);
                break;
            case 'SPPK':
                $eid_doc = Hashids::encode($header->id);
                $sourceShowUrl = url('/showsppks/'.$eid_doc);
                break;
            case 'SPPT':
                $eid_doc = Hashids::encode($header->id);
                $sourceShowUrl = url('/showsppts/'.$eid_doc);
                break;
        }

        // ===== Build URL untuk show BQ (BQ awal) dari SPPJ/SPPT =====
        $bqShowUrl = null;
        $bqHeader = null;

        if (in_array($doc, ['SPPJ', 'SPPT'], true) && !empty($header) && !empty($header->bqid)) {
            // asumsi kolom bq number di tabel bq adalah "bqid"
            // kalau nama kolomnya beda (mis: bqno / bq_id), ganti di where ini
            $bqHeader = Bq::where('bqid', $header->bqid)->first();

            if ($bqHeader) {
                $eid_bq = Hashids::encode($bqHeader->id);
                $bqShowUrl = url('/showbqsppjs/'.$eid_bq);
            }
        }

        return view('pages.canvass.editcs', [
            'eid' => $eid,
            'doc' => $doc,
            'src_id' => $header->id,
            'docno' => $docno,
            'header' => $header ?? $cs,
            'items' => $items,
            'attachment' => $attachment,
            'attachmentCS' => $attachmentCS,
            'cs' => $cs,
            'tops' => $tops,
            // payload untuk preload JS
            'vendorsUsed' => $vendorsUsed,
            'detailVendorMatrix' => $detailVendorMatrix,
            'bq' => $bq,
            'bq_eid' => $bq_eid,
            'csVendorTotals' => $csVendorTotals,
            'bqVendorTotals' => $bqVendorTotals,
            'sourceShowUrl' => $sourceShowUrl,
            'bqShowUrl' => $bqShowUrl,
        ]);
    }

    public function updateCS(Request $request, $csid)
    {
        // dd($request->all());
        // 1) Validasi payload dasar
        $request->validate([
            'doc' => 'required|string',     // SPPB|SPPJ|SPPK|SPPT
            'src_id' => 'nullable',           // penting saat submit (untuk ordered/budget)
            'sppbjktid' => 'nullable|string',
            'cpny_id' => 'required|string',
            'department_id' => 'required|string',
            'bqid' => 'nullable|string',
            'user_peminta' => 'nullable|string',
            'csnote' => 'nullable|string',
            'assigndate' => 'nullable|string',
            'vendors' => 'required|string', // JSON array
            'details' => 'required|string', // JSON array
            'action' => 'nullable|in:save,submit',
        ]);

        // 2) Decode JSON dari form
        // $vendors = json_decode($request->input('vendors', '[]'), true) ?: [];
        // $details = json_decode($request->input('details', '[]'), true) ?: [];

        $vendors = json_decode($request->input('vendors', '[]'), true) ?: [];
        $details = json_decode($request->input('details', '[]'), true) ?: [];

        $round2 = fn ($n) => round((float) $n, 2);

        $docSelectedGrand = collect($vendors)->sum(function ($v) use ($round2) {
            return $round2($v['selected_grand'] ?? 0);
        });

        // === Ambil inventoryid unik dari payload ===
        $invIds = collect($details)
            ->pluck('inventoryid')
            ->filter()
            ->unique()
            ->values()
            ->all();

        // === Ambil harga PO terakhir per inventory ===
        $lastPriceMap = [];

        if (!empty($invIds)) {
            $rows = TrPoLastPrice::query()
                ->select('inventoryid', 'unitcost', 'podate', 'created_at')
                ->whereIn('inventoryid', $invIds)
                ->whereNull('deleted_at')
                ->orderByDesc('podate')
                ->orderByDesc('created_at')
                ->get()
                ->groupBy('inventoryid');

            foreach ($rows as $inventoryid => $items) {
                $lastPriceMap[$inventoryid] = round((float) ($items->first()->unitcost ?? 0), 2);
            }
        }

        // 3) Context user & waktu
        $user = $request->user();
        $username = $user->username ?? 'system';
        $dt = \Carbon\Carbon::now();

        $round2 = fn ($n) => round((float) $n, 2);
        $safeSet = function ($model, string $table, string $column, $value) {
            if (Schema::connection('pgsql')->hasColumn($table, $column)) {
                $model->{$column} = $value;
            }
        };

        $doctype = 'CS';
        $doc = strtoupper($request->input('doc'));
        $srcId = $request->input('src_id');
        $cpnyId = $request->input('cpny_id');
        $deptId = $request->input('department_id');

        // 4) Pastikan line approval tersedia
        $approvalCtl = app(ApprovalController::class);
        $approvalCtl->loadLines($doctype, $cpnyId, $deptId);

        \DB::connection('pgsql')->beginTransaction();
        try {
            // 5) Lock header CS
            /** @var TrCS $cs */
            $cs = TrCS::on('pgsql')
                ->lockForUpdate()
                ->where('csid', $csid)
                ->firstOrFail();

            $csTable = $cs->getTable();
            $prev_csid = $cs->prev_csid;   // <-- penentu: CS awal vs revisi

            // 6) Ambil sumber (header+detail) untuk fallback field tampilan
            $srcHeader = null;
            $srcDetails = collect();
            $srcLineKey = null;

            switch ($doc) {
                case 'SPPB':
                    $srcHeader = TrSPPB::with(['requestType', 'creator', 'purchaser'])->find($srcId);
                    $srcLineKey = 'sppb_no';
                    $srcDetails = $srcHeader
                        ? TrSPPBdetail::where('sppbid', $srcHeader->sppbid)->orderBy($srcLineKey)->get()
                        : collect();
                    break;
                case 'SPPJ':
                    $srcHeader = TrSPPJ::with(['requestType', 'creator', 'purchaser'])->find($srcId);
                    $srcLineKey = 'sppj_no';
                    $srcDetails = $srcHeader
                        ? TrSPPJdetail::where('sppjid', $srcHeader->sppjid)->orderBy($srcLineKey)->get()
                        : collect();
                    break;
                case 'SPPK':
                    $srcHeader = TrSPPK::with(['requestType', 'creator', 'purchaser'])->find($srcId);
                    $srcLineKey = 'sppk_no';
                    $srcDetails = $srcHeader
                        ? TrSPPKdetail::where('sppkid', $srcHeader->sppkid)->orderBy($srcLineKey)->get()
                        : collect();
                    break;
                case 'SPPT':
                    $srcHeader = TrSPPT::with(['requestType', 'creator', 'purchaser'])->find($srcId);
                    $srcLineKey = 'sppt_no';
                    $srcDetails = $srcHeader
                        ? TrSPPTdetail::where('spptid', $srcHeader->spptid)->orderBy($srcLineKey)->get()
                        : collect();
                    break;
                default:
                    abort(422, 'Invalid doc type');
            }

            // index detail sumber → untuk fallback field detail
            $srcIndex = [];
            foreach ($srcDetails as $sd) {
                $key = strtoupper(trim($sd->inventoryid ?? '')).'|'.
                    strtoupper(trim($sd->uom ?? '')).'|'.
                    strtoupper(trim($sd->inventory_descr ?? ''));
                $srcIndex[$key] = $sd;
            }

            // 7) Update HEADER TrCS (termasuk kolom vendor*)
            $cs->sppbjktid = $request->input('sppbjktid');
            $cs->cpny_id = $cpnyId;
            $cs->bqid = $request->input('bqid') ?: ($srcHeader->bqid ?? $cs->bqid);
            $cs->department_id = $deptId ?: ($srcHeader->department_id ?? $cs->department_id);
            $cs->csnote = $request->input('csnote') ?: null;
            $cs->assigndate = $request->input('assigndate') ?: null;

            // lengkapi dari sumber jika kolom ada
            $safeSet($cs, $csTable, 'budget_perpost', $srcHeader->budget_perpost ?? null);
            $safeSet($cs, $csTable, 'woid', $srcHeader->woid ?? null);
            $safeSet($cs, $csTable, 'spbid', $srcHeader->spbid ?? null);

            // Tulis ulang vendor header & reset kolom selected
            // Tulis ulang vendor header & reset kolom selected
            for ($slot = 1; $slot <= 6; ++$slot) {
                $v = $vendors[$slot - 1] ?? null;

                // ===== vendornote: trim + kosong jadi null + limit panjang =====
                $vendorNote = $v['vendornote'] ?? null;
                if ($vendorNote !== null) {
                    $vendorNote = trim((string) $vendorNote);
                    if ($vendorNote === '') {
                        $vendorNote = null;
                    }
                    // batasi panjang (sesuaikan jika kamu pakai varchar)
                    if ($vendorNote !== null) {
                        $vendorNote = mb_substr($vendorNote, 0, 500);
                    }
                }

                $safeSet($cs, $csTable, "vendorid{$slot}", $v['vendorid'] ?? null);
                $safeSet($cs, $csTable, "vendorname{$slot}", $v['vendorname'] ?? null);
                $safeSet($cs, $csTable, "vendoralamat{$slot}", $v['vendoralamat'] ?? null);
                $safeSet($cs, $csTable, "vendortelp{$slot}", $v['vendortelp'] ?? null);
                $safeSet($cs, $csTable, "vendorcp{$slot}", $v['vendorcp'] ?? null);
                $safeSet($cs, $csTable, "vendortop{$slot}", $v['vendortop'] ?? null);

                // ✅ vendor note masuk ke vendornote1..6
                $safeSet($cs, $csTable, "vendornote{$slot}", $vendorNote);

                $safeSet($cs, $csTable, "totalvendor{$slot}", $round2($v['total'] ?? 0));
                $safeSet($cs, $csTable, "taxcodevendor{$slot}", $v['taxcode'] ?? null);
                $safeSet($cs, $csTable, "ppnvendor{$slot}", $round2($v['ppn'] ?? 0));
                $safeSet($cs, $csTable, "pphvendor{$slot}", $round2($v['pph'] ?? 0));
                $safeSet($cs, $csTable, "taxvendor{$slot}", $round2($v['tax'] ?? 0));
                $safeSet($cs, $csTable, "grandtotalvendor{$slot}", $round2($v['grand'] ?? 0));

                // reset kolom selected
                // $safeSet($cs, $csTable, "totalselectedvendor{$slot}",      0);
                // $safeSet($cs, $csTable, "taxselectedvendor{$slot}",        0);
                // $safeSet($cs, $csTable, "grandtotalselectedvendor{$slot}", 0);
                $safeSet($cs, $csTable, "totalselectedvendor{$slot}", $round2($v['selected_total'] ?? 0));
                $safeSet($cs, $csTable, "taxselectedvendor{$slot}", $round2($v['selected_tax'] ?? 0));
                $safeSet($cs, $csTable, "grandtotalselectedvendor{$slot}", $round2($v['selected_grand'] ?? 0));
            }

            if (Schema::connection('pgsql')->hasColumn($csTable, 'updated_by')) {
                $cs->updated_by = $username;
            }
            $cs->save();

            // 8) Replace DETAIL TrCSdetail & akumulasi ke header
            TrCSdetail::on('pgsql')->where('csid', $csid)->delete();

            $lineNo = 0;
            // $docSelectedGrand = 0.0;
            // $selectedByVendor = [];
            // for ($i = 1; $i <= 6; $i++) {
            //     $selectedByVendor[$i] = ['total'=>0.0,'tax'=>0.0,'grand'=>0.0];
            // }

            foreach ($details as $d) {
                ++$lineNo;

                $matchKey = strtoupper(trim($d['inventoryid'] ?? '')).'|'.
                            strtoupper(trim($d['uom'] ?? '')).'|'.
                            strtoupper(trim($d['inventory_descr'] ?? ''));
                $src = $srcIndex[$matchKey] ?? ($srcDetails[$lineNo - 1] ?? null);
                $srcRefNo = $src ? ($src->{$srcLineKey} ?? null) : null;

                $det = new TrCSdetail();
                $det->setConnection('pgsql');

                $det->csid = $csid;
                $det->sppbjktid = $request->input('sppbjktid');
                $det->cs_no = $lineNo;
                $det->sppbjkt_no = $srcRefNo;

                $det->inventory_type = $d['inventory_type'] ?? ($src->inventory_type ?? null);
                $det->inventoryid = $d['inventoryid'] ?? ($src->inventoryid ?? null);
                $det->inventory_descr = $d['inventory_descr'] ?? ($src->inventory_descr ?? null);
                $det->inventory_sub_type = $d['inventory_sub_type'] ?? ($src->inventory_sub_type ?? null);
                $det->inventory_category = $d['inventory_category'] ?? ($src->inventory_category ?? null);

                $det->qty = $round2($d['qty'] ?? ($src->qty ?? 0));
                $det->uom = $d['uom'] ?? ($src->uom ?? null);
                $det->siteid = $d['siteid'] ?? ($src->siteid ?? null);

                $det->type_multiplier = $src->type_multiplier ?? null;
                $det->base_multiplier = isset($src->base_multiplier) ? $round2($src->base_multiplier) : null;
                $det->base_qty = isset($src->base_qty) ? $round2($src->base_qty) : null;
                $det->base_uom = $src->base_uom ?? null;

                $det->inventory_last_price = $lastPriceMap[$det->inventoryid] ?? 0;
                $det->csnote_detail = $d['csnote_detail'] ?? ($src->note ?? null);

                $det->location_id = $src->location_id ?? null;
                $det->sub_location_id = $src->sub_location_id ?? null;
                $det->budget_perpost = $src->budget_perpost ?? null;
                $det->budget_cpny_id = $cpnyId;
                $det->budget_business_unit_id = $src->budget_business_unit_id ?? null;
                $det->budget_department_fin_id = $src->budget_department_fin_id ?? null;
                $det->budget_account_id = $src->budget_account_id ?? null;
                $det->budget_activity_id = $src->budget_activity_id ?? null;
                $det->budget_activity_descr = $src->budget_activity_descr ?? null;

                // $selectedGrandThisRow = 0.0;

                // for ($i = 0; $i < min(count($d['vendor'] ?? []), 6); $i++) {
                //     $slot  = $i + 1;
                //     $vrow  = $d['vendor'][$i];
                //     $vid   = $vrow['vendorid'] ?? null;
                //     $price = $round2($vrow['price'] ?? 0);
                //     $total = $round2($vrow['total'] ?? 0);
                //     $ppn   = $round2($vrow['ppn']   ?? 0);
                //     $pph   = $round2($vrow['pph']   ?? 0);
                //     $tax   = $round2($vrow['tax']   ?? ($ppn + $pph));
                //     $grand = $round2($vrow['grand'] ?? ($total + $tax));
                //     $sel   = !empty($vrow['selected']);

                //     $det->{"vendorid{$slot}"}         = $vid;
                //     $det->{"vendorprice{$slot}"}      = $price;
                //     $det->{"vendortotalprice{$slot}"} = $total;
                //     $det->{"vendor{$slot}selected"}   = (bool)$sel;

                //     // if ($sel) {
                //     //     $selectedGrandThisRow = $grand;
                //     //     $selectedByVendor[$slot]['total'] += $total;
                //     //     $selectedByVendor[$slot]['tax']   += $tax;
                //     //     $selectedByVendor[$slot]['grand'] += $grand;
                //     // }
                // }
                // $docSelectedGrand = collect($vendors)->sum(function ($v) use ($round2) {
                //     return $round2($v['selected_grand'] ?? 0);
                // });

                for ($i = 0; $i < min(count($d['vendor'] ?? []), 6); ++$i) {
                    $slot = $i + 1;
                    $vrow = $d['vendor'][$i];
                    $vid = $vrow['vendorid'] ?? null;
                    $price = $round2($vrow['price'] ?? 0);
                    $total = $round2($vrow['total'] ?? 0);
                    $sel = !empty($vrow['selected']);

                    $det->{"vendorid{$slot}"} = $vid;
                    $det->{"vendorprice{$slot}"} = $price;
                    $det->{"vendortotalprice{$slot}"} = $total;
                    $det->{"vendor{$slot}selected"} = (bool) $sel;
                }

                // $docSelectedGrand += $selectedGrandThisRow;

                $det->status = 'H';   // draft dulu, jadi 'P' saat submit
                $det->created_by = $username;
                $det->save();
            }

            // 9) Update header selected vendor
            // for ($slot = 1; $slot <= 6; $slot++) {
            //     $safeSet($cs, $csTable, "totalselectedvendor{$slot}",      $round2($selectedByVendor[$slot]['total']));
            //     $safeSet($cs, $csTable, "taxselectedvendor{$slot}",        $round2($selectedByVendor[$slot]['tax']));
            //     $safeSet($cs, $csTable, "grandtotalselectedvendor{$slot}", $round2($selectedByVendor[$slot]['grand']));
            // }
            $cs->save();

            // 10) Attachments BARU
            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr' => $cs->csid,
                    'doctype' => $doctype,
                    'cpnyid' => $cpnyId,
                    'departementid' => $deptId,
                    'base_folder' => 'att-purchasing-app/'.strtolower($doctype),
                    'created_by' => $username,
                ];
                $files = (array) $request->file('attachments');

                try {
                    $uploader = app(TrAttachmentController::class);
                    $uploader->uploadInternal($meta, $files);
                } catch (\Throwable $e) {
                    \DB::connection('pgsql')->rollBack();

                    return response()->json([
                        'ok' => false,
                        'message' => 'Gagal upload attachment: '.$e->getMessage(),
                    ], 500);
                }
            }

            // 11) SAVE vs SUBMIT
            $action = strtolower($request->input('action', 'save'));
            if (!in_array($action, ['save', 'submit'], true)) {
                $action = 'save';
            }

            if ($action === 'submit') {
                if (empty($prev_csid)) {
                    // CS AWAL → flow lama

                    // (a) Validasi submit server-side
                    $this->validateSubmitServerSide($details);

                    // (b) Update ordered/openordered pada dokumen sumber
                    $this->updateOrderedOnSource($details, $srcHeader, $srcDetails, $srcIndex, $cpnyId);

                    if ($cs->bqtype !== 'Kontrak') {
                        // (c) Reserve budget via SP (Submit)
                        $this->reserveBudget('CS', $cs->csid,$cpnyId, 'Submit', $username);
                    }
                } else {
                    // CS REVISI → update ke TrPOReuse (dan header PO) saja
                    $this->updateOrderedOnPOReuse($details, $prev_csid, $cpnyId);
                }

                // (d) Set status header & detail = Pending, set submitdate
                $cs->status = 'P';
                if (Schema::connection('pgsql')->hasColumn($csTable, 'submitdate')) {
                    $cs->submitdate = $dt;
                }
                if (Schema::connection('pgsql')->hasColumn($csTable, 'updated_by')) {
                    $cs->updated_by = $username;
                }
                $cs->save();

                TrCSdetail::on('pgsql')->where('csid', $csid)->update(['status' => 'P']);

                // (e) Generate TrApproval + email approver pertama
                $ctx = [
                    'ignore_nominal' => false,
                    'grand_total' => (float) $docSelectedGrand,
                ];

                [$firstApprovalUsernames, $linesCount] = $approvalCtl->generateForDocument(
                    $cs->csid,
                    $doctype,
                    $cpnyId,
                    $deptId,
                    $username,
                    $ctx,
                    $dt
                );

                if ($firstApprovalUsernames) {
                    $cs->completed_by = $firstApprovalUsernames;
                    $cs->completed_at = $dt;
                    $cs->save();
                }

                $this->applyFastApproveForCS($cs->csid, $username, $dt);

                $eid = Hashids::encode($cs->id);
                $approvalCtl->notifyFirstApprover(
                    $cs->csid,
                    $doctype,
                    $cs->status,  // 'P'
                    'CS',
                    url('/showcs/'.$eid),
                    [
                        'info' => $cs->csnote ?: ($srcHeader->keperluan ?? ''),
                        'createdby' => $cs->created_by,
                        'date' => $dt->toDateTimeString(),
                    ]
                );
            } else {
                // SAVE saja → tetap status draft
                if (!$cs->status || $cs->status === 'H') {
                    $cs->status = 'H';
                    if (Schema::connection('pgsql')->hasColumn($csTable, 'updated_by')) {
                        $cs->updated_by = $username;
                    }
                    $cs->save();
                }
            }

            \DB::connection('pgsql')->commit();

            return response()->json([
                'ok' => true,
                'message' => $action === 'submit'
                                    ? 'CS berhasil diupdate & diajukan'
                                    : 'CS berhasil diupdate',
                'csid' => $cs->csid,
                'grand_total' => $round2($docSelectedGrand),
                'status' => $cs->status,
                'submitdate' => optional($cs->submitdate)->toDateTimeString(),
            ]);
        } catch (\Throwable $e) {
            \DB::connection('pgsql')->rollBack();
            report($e);

            return response()->json([
                'ok' => false,
                'message' => 'Gagal update CS: '.(config('app.debug') ? $e->getMessage() : 'Terjadi kesalahan'),
            ], 500);
        }
    }

    public function showCS($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $cs = TrCS::with([
            'creator:username,name',
            'updater:username,name',
            'completer:username,name',
        ])->findOrFail($id);

        $csdetail = TrCSdetail::with([
            'location:location_id,location_name',
            'subLocation:sub_location_id,sub_location_name',
        ])
        ->where('csid', $cs->csid)
        ->whereNotNull('qty')           // aman kalau kolom bisa null
        ->where('qty', '!=', 0)         // terjemah ke SQL: qty <> 0
        ->orderBy('cs_no')
        ->get();

        // ===== Last Price Map (inventoryid => latest unitcost) =====
        $invIds = $csdetail->pluck('inventoryid')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $lastUnitcostMap = [];

        if (!empty($invIds)) {
            $rowsLast = TrPoLastPrice::query()
                ->select('inventoryid', 'unitcost', 'podate', 'created_at')
                ->whereIn('inventoryid', $invIds)
                ->whereNull('deleted_at')
                ->orderByDesc('podate')        // terbaru
                ->orderByDesc('created_at')    // tie breaker
                ->get();

            $lastUnitcostMap = $rowsLast
                ->groupBy('inventoryid')
                ->map(fn ($g) => (float) ($g->first()->unitcost ?? 0))
                ->toArray();
        }

        // inject ke tiap csdetail (biar blade gampang)
        $csdetail = $csdetail->map(function ($d) use ($lastUnitcostMap) {
            $invId = $d->inventoryid ?? null;
            $d->last_unitcost = $invId ? ($lastUnitcostMap[$invId] ?? 0) : 0;

            return $d;
        });

        // ---------- ambil lampiran dari tr_attachment ----------
        $rows = TrAttachment::where('refnbr', $cs->csid)
            ->where('status', 'A')
            ->orderBy('created_at', 'desc')
            ->get();

        // siapkan Signed URL dari GCS
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

        // map jadi data siap pakai di view
        $attachmentCS = $rows->map(function ($r) use ($bucket) {
            $objectPath = rtrim($r->folder, '/').'/'.$r->filename;   // ex: att-purchasing-app/wo/2025/xxxx-file.pdf
            $object = $bucket->object($objectPath);

            // Signed URL 10 menit
            $signedUrl = null;
            try {
                $signedUrl = $object->signedUrl(
                    new \DateTimeImmutable('+10 minutes'),
                    ['version' => 'v4']
                );
            } catch (\Throwable $e) {
                // kalau gagal signed URL, biarkan null; di UI tampilkan nama saja
                \Log::warning('Signed URL gagal', ['path' => $objectPath, 'error' => $e->getMessage()]);
            }

            return (object) [
                'display_name' => $r->attachment_name,         // nama yang enak dibaca
                'created_by' => $r->created_by,
                'created_at' => $r->created_at,
                'url' => $signedUrl,                  // bisa null jika gagal
                'folder' => $r->folder,
                'filename' => $r->filename,
                'extention' => $r->extention,
                'size' => $r->filesize,
            ];
        });

        // ---------- ambil lampiran dari tr_attachment ----------
        $rows = TrAttachment::where('refnbr', $cs->sppbjktid)
            ->where('status', 'A')
            ->orderBy('created_at', 'desc')
            ->get();

        // siapkan Signed URL dari GCS
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

        // map jadi data siap pakai di view
        $attachmentBJKT = $rows->map(function ($r) use ($bucket) {
            $objectPath = rtrim($r->folder, '/').'/'.$r->filename;   // ex: att-purchasing-app/wo/2025/xxxx-file.pdf
            $object = $bucket->object($objectPath);

            // Signed URL 10 menit
            $signedUrl = null;
            try {
                $signedUrl = $object->signedUrl(
                    new \DateTimeImmutable('+10 minutes'),
                    ['version' => 'v4']
                );
            } catch (\Throwable $e) {
                // kalau gagal signed URL, biarkan null; di UI tampilkan nama saja
                \Log::warning('Signed URL gagal', ['path' => $objectPath, 'error' => $e->getMessage()]);
            }

            return (object) [
                'display_name' => $r->attachment_name,         // nama yang enak dibaca
                'created_by' => $r->created_by,
                'created_at' => $r->created_at,
                'url' => $signedUrl,                  // bisa null jika gagal
                'folder' => $r->folder,
                'filename' => $r->filename,
                'extention' => $r->extention,
                'size' => $r->filesize,
            ];
        });

        // / =========================
        // Ambil header sumber (SPPB/J/K/T) dari 2 huruf depan sppbjktid
        // =========================
        $prefix = strtoupper(substr((string) $cs->sppbjktid, 0, 2));

        $srcHeader = null;
        $srcDetails = null;
        $docid = null;

        if ($prefix == 'PB') {
            $srcHeader = TrSPPB::with(['requestType', 'creator', 'purchaser'])->where('sppbid', $cs->sppbjktid)->first();
            $srcDetails = TrSPPBdetail::where('sppbid', $cs->sppbjktid)->get();
            $docid = $srcHeader->sppbid;
        } elseif ($prefix == 'PJ') {
            $srcHeader = TrSPPJ::with(['requestType', 'creator', 'purchaser'])->where('sppjid', $cs->sppbjktid)->first();
            $srcDetails = TrSPPJdetail::where('sppjid', $cs->sppbjktid)->get();
            $docid = $srcHeader->sppjid;
        } elseif ($prefix == 'PK') {
            $srcHeader = TrSPPK::with(['requestType', 'creator', 'purchaser'])->where('sppkid', $cs->sppbjktid)->first();
            $srcDetails = TrSPPKdetail::where('sppkid', $cs->sppbjktid)->get();
            $docid = $srcHeader->sppkid;
        } elseif ($prefix == 'PT') {
            $srcHeader = TrSPPT::with(['requestType', 'creator', 'purchaser'])->where('spptid', $cs->sppbjktid)->first();
            $srcDetails = TrSPPTdetail::where('spptid', $cs->sppbjktid)->get();
            $docid = $srcHeader->spptid;
        } else {
            abort(422, 'Invalid doc type');
        }

        // kalau srcHeader tidak ketemu, jangan fatal error di encode
        $eid_sppbjkt = $srcHeader ? Hashids::encode($srcHeader->id) : null;

        // ---- BQ (khusus PJ/PT) ----
        $eid_bq = null;
        if (in_array($prefix, ['PJ', 'PT'], true) && !empty($cs->bqid)) {
            $bqcs = TrBQCS::where('bqid', $cs->bqid)->first();
            $eid_bq = $bqcs ? Hashids::encode($bqcs->id) : null;
        }

        // ---- Prev CS (AMAN null) ----
        $eid_cs_prev = null;
        if (!empty($cs->prev_csid)) {
            $cs_prev = TrCS::where('csid', $cs->prev_csid)->first(); // <- pakai csid yg direferensikan
            $eid_cs_prev = $cs_prev ? Hashids::encode($cs_prev->id) : null;
        }

        // ---- IMBudget (AMAN null) ----
        $eid_imbudget = null;
        $imbudget = null;
        if (!empty($cs->imbudgetid)) {
            $imbudget = TrIMBudget::where('imbudgetid', $cs->imbudgetid)->first();
            $eid_imbudget = $imbudget ? Hashids::encode($imbudget->id) : null;
        }

        // ---- susun vendor header: maksimal 6 kolom ----
        $vendors = [];
        for ($i = 1; $i <= 6; ++$i) {
            $vid = $cs->{"vendorid{$i}"} ?? null;
            if (!$vid) {
                continue;
            }
            $vendors[] = [
                'i' => $i,
                'vendorid' => $vid,
                'vendorname' => $cs->{"vendorname{$i}"} ?? '',
                'vendoralamat' => $cs->{"vendoralamat{$i}"} ?? '',
                'vendortelp' => $cs->{"vendortelp{$i}"} ?? '',
                'vendorcp' => $cs->{"vendorcp{$i}"} ?? '',
                'vendortop' => $cs->{"vendortop{$i}"} ?? '',
                'vendornote' => $cs->{"vendornote{$i}"} ?? '',
                'ppn' => (float) ($cs->{"ppnvendor{$i}"} ?? 11.00),
                'pph' => (float) ($cs->{"pphvendor{$i}"} ?? 0.00),
                'total' => (float) ($cs->{"totalvendor{$i}"} ?? 0),
                'grand' => (float) ($cs->{"grandtotalvendor{$i}"} ?? 0),
                'selected_total' => (float) ($cs->{"totalselectedvendor{$i}"} ?? 0),
                'selected_grand' => (float) ($cs->{"grandtotalselectedvendor{$i}"} ?? 0),
                'taxcode' => $cs->{"taxcodevendor{$i}"} ?? '',
            ];
        }

        // $loginUsername = $user->username ?? $user->name ?? null;
        // $canUpload     = $cs->created_by === $loginUsername;
        $loginUsername = $user->username ?? $user->name ?? null;

        // created_by boleh upload
        $isCreator = $cs->created_by === $loginUsername;

        // approver waiting approval juga boleh upload
        $isWaitingApprover = false;

        if ($loginUsername) {
            $isWaitingApprover = TrApproval::query()
                ->where('refnbr', $cs->csid) // kalau approval CS pakai csid
                // ->where('status', 'P')
                // ->whereNotNull('aprv_datebefore')
                ->where(function ($q) use ($loginUsername) {
                    $u = trim((string) $loginUsername);

                    $q->where('aprv_username', $u)
                    ->orWhere('aprv_username', 'ilike', $u.',%')
                    ->orWhere('aprv_username', 'ilike', '%,'.$u.',%')
                    ->orWhere('aprv_username', 'ilike', '%,'.$u);
                })
                ->exists();
        }

        $canUpload = $isCreator || $isWaitingApprover;

        return view('pages.canvass.showcs', [
            'cs' => $cs,
            'attachmentCS' => $attachmentCS,
            'attachmentBJKT' => $attachmentBJKT,
            'csdetail' => $csdetail,
            'vendors' => $vendors,
            'srcHeader' => $srcHeader,
            'docid' => $docid,
            'prefix' => $prefix,
            'hash' => $hash,
            'eid_sppbjkt' => $eid_sppbjkt,
            'eid_bq' => $eid_bq,
            'canUpload' => $canUpload,
            'eid_cs_prev' => $eid_cs_prev,
            'eid_imbudget' => $eid_imbudget,
        ]);
    }

    public function approveCS(Request $request, $docid)
    {
        $user = $request->user();
        $doctype = 'CS';

        $cs = TrCS::with('creator')->where('csid', $docid)->first();
        if (!$cs) {
            return response()->json(['success' => false, 'message' => 'CS not found'], 404);
        }

        // Sumber header asal (PB/PJ/PK/PT) → tetap seperti semula
        $prefix = strtoupper(substr((string) $cs->sppbjktid, 0, 2));
        $srcHeader = null;
        $potype = null;

        if ($prefix === 'PB') {
            $srcHeader = TrSPPB::with(['requestType', 'creator', 'purchaser'])
                ->where('sppbid', $cs->sppbjktid)->first();
            $potype = 'PO';
        } elseif ($prefix === 'PJ') {
            $srcHeader = TrSPPJ::with(['requestType', 'creator', 'purchaser'])
                ->where('sppjid', $cs->sppbjktid)->first();
            $potype = 'SPK';
        } elseif ($prefix === 'PK') {
            $srcHeader = TrSPPK::with(['requestType', 'creator', 'purchaser'])
                ->where('sppkid', $cs->sppbjktid)->first();
            $potype = 'SPK';
        } elseif ($prefix === 'PT') {
            $srcHeader = TrSPPT::with(['requestType', 'creator', 'purchaser'])
                ->where('spptid', $cs->sppbjktid)->first();
            $potype = 'SPK';
        } else {
            return response()->json(['success' => false, 'message' => 'Invalid doc type'], 422);
        }

        $eid = Hashids::encode($cs->id);
        $docUrl = url('/showcs/'.$eid);
        $fullname = data_get($cs, 'creator.name') ?: $cs->created_by;

        // ======== LOGIKA IMBUDGET ========
        // Ambil level approver saat ini
        $uname = (string) ($user->username ?? '');

        $pending = TrApproval::where('refnbr', $cs->csid)
            ->where('status', 'P')
            ->whereNotNull('aprv_datebefore')
            // aprv_username bisa "indrawancahyadi,williemhalim" => cari yg mengandung username login
            ->whereRaw('aprv_username ILIKE ?', ['%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $uname).'%'])
            ->orderBy('aprv_leveling', 'asc')   // aprv_leveling sudah numeric di PG
            ->orderBy('created_at', 'asc')
            ->first();

        $currentLevel = (int) ($pending->aprv_leveling ?? 0);

        // Threshold setting
        $threshold = (int) (MsPurchSetting::where('setting_id', 'IMGEN')->value('setting_value_int') ?? 0);

        $flagIM = (bool) ($cs->flag_imbudget ?? false);     // kolom di TrCS
        $existingIM = $cs->imbudgetid ?? null;                  // kolom di TrCS
        $statusIM = $cs->status_imbudget ?? null;             // kolom di TrCS
        $needGenerateNow = $flagIM && empty($existingIM) && ($currentLevel >= $threshold);

        // 1) flag=true & sudah punya IM tapi belum Complete → STOP approve
        if ($flagIM && !empty($existingIM) && $statusIM !== 'C') {
            return response()->json([
                'success' => false,
                'code' => 'IM_IN_PROGRESS',
                'message' => 'Tidak bisa approve. Masih On Progress IM.',
            ], 409);
        }

        // 2) flag=true & belum punya IM & level >= threshold → perlu konfirmasi SweetAlert
        if ($needGenerateNow) {
            if (!$request->boolean('confirm_generate_im')) {
                // Minta konfirmasi dulu (frontend munculin SweetAlert)
                return response()->json([
                    'success' => true,
                    'need_confirm_generate_im' => true,
                    'message' => 'Generate IMBudget sekarang?',
                ]);
            }

            // User sudah konfirmasi → Generate IM, status H; update CS (imbudgetid + status_imbudget)
            try {
                // panggil controller generateIMBudget dengan sumber data dari CS
                $payload = new Request([
                    'csid' => $cs->csid,
                    'cpnyid' => $cs->cpny_id ?? $cs->cpnyid,
                    'departementid' => $cs->department_id ?? $cs->departementid,
                    'perpost' => $cs->budget_perpost ?? null,
                    'user_peminta' => $cs->user_peminta ?? $user->username,
                    'sppbjktid' => $cs->sppbjktid,
                    'imbudgetnote' => $cs->csnote ?? $cs->keperluan,
                ]);

                $imCtrl = app(IMBudgetController::class);

                $resp = $imCtrl->generateIMBudget($payload);

                // Jika generateIMBudget mengembalikan status error, teruskan apa adanya ke frontend
                if (method_exists($resp, 'getStatusCode') && $resp->getStatusCode() >= 400) {
                    return $resp; // berisi message/error detail dari generateIMBudget
                }

                // Ambil data JSON (array) dengan aman
                $data = $resp->getData(true) ?? [];

                // Robust extract (kalau suatu saat dibungkus di 'data')
                $imbudgetid = $data['imbudgetid'] ?? ($data['data']['imbudgetid'] ?? null);

                // Kalau tetap tidak ada, lempar pesan yang lebih informatif
                if (!$imbudgetid) {
                    $detail = $data['error'] ?? $data['message'] ?? 'Tidak diketahui';

                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal generate IMBudget: '.$detail,
                    ], 500);
                }

                $imbudgetid = $data['imbudgetid'];

                // set status IM → H (Hold)
                TrIMBudget::where('imbudgetid', $imbudgetid)->update(['status' => 'H']);

                // update CS: nomor im + status_imbudget = H
                $cs->imbudgetid = $imbudgetid;
                $cs->status_imbudget = 'H';
                $cs->save();

                $imb = TrIMBudget::where('imbudgetid', $imbudgetid)->first();
                // $hash = $imb ? \Vinkla\Hashids\Facades\Hashids::encode($imb->id) : null;
                $eidCs = Hashids::encode($cs->id);

                return response()->json([
                    'success' => true,
                    'code' => 'IM_CREATED_HOLD',
                    'message' => "IMBudget berhasil dibuat ($imbudgetid) dan di-HOLD.",
                    'imbudgetid' => $imbudgetid,
                    // 'imbudget_show_url' => $hash ? url('/showimbudgets/' . $hash) : null,
                    'imbudget_show_url' => url('/showcs/'.$eidCs),
                ]);
            } catch (\Throwable $e) {
                \Log::error('Generate IM from approveCS failed', [
                    'csid' => $cs->csid,
                    'err' => $e->getMessage(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Gagal generate IMBudget: '.$e->getMessage(),
                ], 500);
            }
        }

        // 3) flag=true & sudah punya IM & status_imbudget = C → lanjut approve CS
        // 4) flag=false → lanjut approve CS
        $result = app(ApprovalController::class)->approveStep(
            $cs->csid,
            $doctype,
            $user->username,
            $user->name,

            // COMPLETE CALLBACK
            function (string $refnbr, \Carbon\Carbon $now) use ($cs, $fullname, $docUrl, $srcHeader, $potype) {
                $cs->status = 'C';
                $cs->completed_by = $cs->completed_by ?: auth()->user()->username;
                $cs->completed_at = $now;
                $cs->save();

                TrCSdetail::where('csid', $cs->csid)->update(['status' => 'C']);

                try {
                    if (strtoupper((string) ($cs->bqtype ?? '')) === 'KONTRAK') {
                        // dd('kontrak from cs');
                        $this->generateKontrakFromCS($cs, auth()->user());
                    } else {
                        // dd('po from cs');
                        $this->generatePOFromCS($cs, auth()->user(), $potype);
                    }
                } catch (\Throwable $e) {
                    \Log::error('generate from CS failed', [
                        'csid' => $cs->csid,
                        'error' => $e->getMessage(),
                    ]);
                }

                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $cs->csid, 'CS', 'C', $cs->created_by, $docUrl, [
                        'cpnyid' => $cs->cpny_id ?? $cs->cpnyid ?? '',
                        'deptname' => $cs->department_id ?? $cs->departementid ?? '',
                        'date' => $cs->csdate,
                        'info' => optional($srcHeader)->keperluan ?? $cs->keperluan,
                        'fullname' => $fullname,
                        'name' => $fullname,
                        'createdby' => $fullname,
                    ]
                );
            },

            // NEXT APPROVER CALLBACK
            function ($next, \Carbon\Carbon $now) use ($cs, $docUrl) {
                app(ApprovalController::class)->notifyFirstApprover(
                    $cs->csid, 'CS', 'P', 'CS', $docUrl, [
                        'info' => $cs->keperluan,
                        'createdby' => $cs->created_by,
                        'date' => $now->toDateTimeString(),
                    ]
                );
                $cs->completed_by = auth()->user()->username;
                $cs->completed_at = $now;
                $cs->save();
            }
        );

        if (!($result['ok'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Approve failed',
            ], 403);
        }

        return response()->json(['success' => true, 'message' => 'Task approved successfully']);
    }

    public function rejectCS(Request $request, $docid)
    {
        $user = $request->user();
        $username = $user->username;
        $doctype = 'CS';

        $cs = TrCS::with('creator')->where('csid', $docid)->first();
        $cpnyId = $cs->cpny_id;
        if (!$cs) {
            return response()->json(['success' => false, 'message' => 'CS not found'], 404);
        }

        // (opsional) ambil sumber header untuk info keperluan
        $srcHeader = null;
        $prefix = strtoupper(substr((string) $cs->sppbjktid, 0, 2));
        if ($prefix === 'PB') {
            $srcHeader = TrSPPB::with(['requestType', 'creator', 'purchaser'])
                ->where('sppbid', $cs->sppbjktid)->first();
        } elseif ($prefix === 'PJ') {
            $srcHeader = TrSPPJ::with(['requestType', 'creator', 'purchaser'])
                ->where('sppjid', $cs->sppbjktid)->first();
        } elseif ($prefix === 'PK') {
            $srcHeader = TrSPPK::with(['requestType', 'creator', 'purchaser'])
                ->where('sppkid', $cs->sppbjktid)->first();
        } elseif ($prefix === 'PT') {
            $srcHeader = TrSPPT::with(['requestType', 'creator', 'purchaser'])
                ->where('spptid', $cs->sppbjktid)->first();
        }

        $eid = Hashids::encode($cs->id);
        $docUrl = url('/showcs/'.$eid);
        $fullname = data_get($cs, 'creator.name') ?: $cs->created_by;

        $result = app(ApprovalController::class)->rejectStep(
            $cs->csid,           // refnbr
            $doctype,            // CS
            $user->username,     // actor
            $user->name,         // actor

            // CALLBACK saat reject benar-benar dieksekusi
            function (string $refnbr, \Carbon\Carbon $now) use ($cs, $fullname, $docUrl, $srcHeader, $username) {
                \DB::connection('pgsql')->beginTransaction();
                try {
                    if ($cs->bqtype !== 'Kontrak') {
                        // 1) Reserve budget via SP (Reject)
                        $this->reserveBudget('CS', $cs->csid,$cpnyId, 'Reject', $username);
                    }

                    // ✅ 2) Update rejectordered di dokumen sumber (SPPB/SPPJ/SPPK/SPPT)
                    $this->updateRejectOrderedOnSource($cs, auth()->user()->username);

                    // Header -> R
                    $cs->status = 'R';
                    $cs->completed_by = auth()->user()->username;
                    $cs->completed_at = $now;
                    $cs->save();

                    // Email requester
                    app(ApprovalController::class)->notifyRequesterOnStatus(
                        $cs->csid,
                        'CS',
                        'R',
                        $cs->created_by,
                        $docUrl,
                        [
                            'cpnyid' => $cs->cpny_id ?? $cs->cpnyid ?? '',
                            'deptname' => $cs->department_id ?? $cs->departementid ?? '',
                            'date' => $now->toDateString(),
                            'info' => optional($srcHeader)->keperluan ?? $cs->keperluan,
                            'fullname' => $fullname,
                            'name' => $fullname,
                            'createdby' => $fullname,
                        ]
                    );

                    // Simpan komentar (jika ada)
                    try {
                        app('App\Http\Controllers\SendCommentController')->sendmsg($cs->id, 'CS', request());
                    } catch (\Throwable $e) {
                    }

                    \DB::connection('pgsql')->commit();
                } catch (\Throwable $e) {
                    \DB::connection('pgsql')->rollBack();
                    throw $e;
                }
            }
        );

        if (!($result['ok'] ?? false)) {
            return response()->json(['success' => false, 'message' => $result['message'] ?? 'Reject failed'], 403);
        }

        return response()->json(['success' => true, 'message' => 'CS rejected successfully']);
    }

    public function reviseCS(Request $request, $docid)
    {
        $user = $request->user();
        $username = $user->username;
        $doctype = 'CS';

        $cs = TrCS::with('creator')->where('csid', $docid)->first();
        $cpnyId = $cs->cpny_id;
        if (!$cs) {
            return response()->json(['success' => false, 'message' => 'CS not found'], 404);
        }

        // (opsional) ambil sumber header untuk info keperluan
        $srcHeader = null;
        $prefix = strtoupper(substr((string) $cs->sppbjktid, 0, 2));
        if ($prefix === 'PB') {
            $srcHeader = TrSPPB::with(['requestType', 'creator', 'purchaser'])
                ->where('sppbid', $cs->sppbjktid)->first();
        } elseif ($prefix === 'PJ') {
            $srcHeader = TrSPPJ::with(['requestType', 'creator', 'purchaser'])
                ->where('sppjid', $cs->sppbjktid)->first();
        } elseif ($prefix === 'PK') {
            $srcHeader = TrSPPK::with(['requestType', 'creator', 'purchaser'])
                ->where('sppkid', $cs->sppbjktid)->first();
        } elseif ($prefix === 'PT') {
            $srcHeader = TrSPPT::with(['requestType', 'creator', 'purchaser'])
                ->where('spptid', $cs->sppbjktid)->first();
        }

        $eid = Hashids::encode($cs->id);
        $docUrl = url('/showcs/'.$eid);
        $fullname = data_get($cs, 'creator.name') ?: $cs->created_by;

        $result = app(ApprovalController::class)->reviseStep(
            $cs->csid,           // refnbr
            $doctype,            // CS
            $user->username,     // actor
            $user->name,         // actor

            // CALLBACK saat revise benar-benar dieksekusi
            function (string $refnbr, \Carbon\Carbon $now) use ($cs, $fullname, $docUrl, $srcHeader, $username) {
                \DB::connection('pgsql')->beginTransaction();
                try {
                    if ($cs->bqtype !== 'Kontrak') {
                        // 1) Reserve budget via SP (Revise)
                        $this->reserveBudget('CS', $cs->csid, $cpnyId,'Revise', $username);
                    }

                    // ✅ 2) rollback ordered/openordered ke dokumen sumber
                    $this->rollbackOrderedOnSourceForRevise($cs, auth()->user()->username);

                    // Header -> H
                    $cs->status = 'H';
                    $cs->completed_by = auth()->user()->username;
                    $cs->completed_at = $now;
                    $cs->save();

                    // Email requester
                    app(ApprovalController::class)->notifyRequesterOnStatus(
                        $cs->csid,
                        'CS',
                        'D',
                        $cs->created_by,
                        $docUrl,
                        [
                            'cpnyid' => $cs->cpny_id ?? $cs->cpnyid ?? '',
                            'deptname' => $cs->department_id ?? $cs->departementid ?? '',
                            'date' => $now->toDateString(),
                            'info' => optional($srcHeader)->keperluan ?? $cs->keperluan,
                            'fullname' => $fullname,
                            'name' => $fullname,
                            'createdby' => $fullname,
                        ]
                    );

                    try {
                        app('App\Http\Controllers\SendCommentController')->sendmsg($cs->id, 'CS', request());
                    } catch (\Throwable $e) {
                    }

                    \DB::connection('pgsql')->commit();
                } catch (\Throwable $e) {
                    \DB::connection('pgsql')->rollBack();
                    throw $e;
                }
            }
        );

        if (!($result['ok'] ?? false)) {
            return response()->json(['success' => false, 'message' => $result['message'] ?? 'Revise failed'], 403);
        }

        return response()->json(['success' => true, 'message' => 'CS revised successfully']);
    }

    public function tracking($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $cs = TrCS::findOrFail($id);

        $getName = function (?string $username) {
            if (!$username) {
                return null;
            }
            $u = User::where('username', $username)->first();

            return $u->name ?? $username;
        };

        $createdByName = $getName($cs->created_by ?? null);
        $createdAt = $cs->created_at ? \Carbon\Carbon::parse($cs->created_at)->format('Y-m-d H:i') : null;

        $completedByName = $getName($cs->completed_by ?? null);
        $completedAt = $cs->completed_at ? \Carbon\Carbon::parse($cs->completed_at)->format('Y-m-d H:i') : null;

        // kolom opsional, kalau tidak ada biarkan null
        $rejectedByName = $getName($cs->rejected_by ?? null);
        $rejectedAt = isset($cs->rejected_at) ? \Carbon\Carbon::parse($cs->rejected_at)->format('Y-m-d H:i') : null;

        $revisedByName = $getName($cs->revised_by ?? null);
        $revisedAt = isset($cs->revised_at) ? \Carbon\Carbon::parse($cs->revised_at)->format('Y-m-d H:i') : null;

        $status = (string) ($cs->status ?? '');
        $labelMap = [
            'P' => 'Waiting approval',
            'R' => 'Rejected',
            'D' => 'Revise',
            'C' => 'Completed',
        ];
        $statusLabel = $labelMap[$status] ?? $status;

        // selalu mulai dari Submitted
        $steps = [[
            'key' => 'submitted',
            'title' => 'CS',
            'status' => 'C',              // dibuat = completed
            'status_label' => 'Submitted',
            'by' => $createdByName,
            'at' => $createdAt,
        ]];

        switch ($status) {
            case 'P':
                // masih menunggu/berjalan → tampilkan Approval saja
                $steps[] = [
                    'key' => 'approval',
                    'title' => 'Approval',
                    'status' => 'P',
                    'status_label' => 'Waiting approval',
                    'by' => $completedByName,
                    'at' => $completedAt,
                ];
                break;

            case 'R':
                // DITOLAK → langsung Submitted → Rejected (tanpa Approval)
                $steps[] = [
                    'key' => 'rejected',
                    'title' => 'Rejected',
                    'status' => 'R',
                    'status_label' => 'Rejected',
                    'by' => $completedByName,
                    'at' => $completedAt,
                ];
                break;

            case 'D':
                // REVISE → Submitted → Revise
                $steps[] = [
                    'key' => 'revise',
                    'title' => 'Revise',
                    'status' => 'D',
                    'status_label' => 'Revise',
                    'by' => $completedByName,
                    'at' => $completedAt,
                ];
                break;

            case 'C':
                // SELESAI → bisa langsung Submitted → Completed
                // (kalau kamu ingin menampilkan Approval yang sudah dilalui,
                // tambahkan step 'approval' sebelum 'completed')
                $steps[] = [
                    'key' => 'completed',
                    'title' => 'Completed',
                    'status' => 'C',
                    'status_label' => 'Completed',
                    'by' => $completedByName,
                    'at' => $completedAt,
                ];
                break;

            default:
                // status tidak dikenal → biarkan hanya Submitted
                break;
        }

        return response()->json([
            'doc' => $cs->csid ?? (string) $cs->id,
            'steps' => $steps,
            'status' => $status,
            'status_label' => $statusLabel,
        ]);
    }

    public function printCS($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $authUser = Auth::user();
        if (!$authUser) {
            return redirect()->route('login');
        }

        // Header CS + relasi
        $cs = TrCS::with([
            'creator:username,name',
            'updater:username,name',
            'completer:username,name',
        ])->findOrFail($id);

        // Detail
        $csdetail = TrCSdetail::with([
            'location:location_id,location_name',
            'subLocation:sub_location_id,sub_location_name',
        ])->where('csid', $cs->csid)->orderBy('cs_no')->get();

        // Approval
        //  $approval = TrApproval::query()
        //     ->where('refnbr', $cs->csid)          // dulu: docid
        //     ->where('status', '<>', 'X')
        //     ->orderByRaw('CAST(aprv_leveling AS numeric) ASC')
        //     ->orderBy('created_at', 'ASC')            // tie-breaker kalau leveling sama
        //     ->get();
        $refnbr = $cs->csid;
        $apprTable = (new TrApproval())->getTable(); // "tr_approval"

        $approval = TrApproval::query()
            ->where('refnbr', $refnbr)           
            ->where('status', '<>', 'X')
            ->reorder()
            ->orderBy('created_at', 'asc')
            ->orderBy('aprv_leveling', 'asc')
            ->orderBy('id', 'asc')
            ->get([
                'aprv_leveling',
                'aprv_name',
                'aprv_datebefore',
                'aprv_dateafter',
                'status',
                'aprv_type',
                'aprv_condition',
            ]);

        $approve_count = $approval->count();

        // Company
        $company = MsCompany::where('cpny_id', $cs->cpny_id)->first();

        // Map status
        switch ($cs->status) {
            case 'R':
                $status_doc = 'Rejected';
                break;
            case 'C':
                $status_doc = 'Completed';
                break;
            case 'D':
                $status_doc = 'Hold';
                break;
            case 'X':
                $status_doc = 'Cancel';
                break;
            default:
                $status_doc = 'On Progress';
                break;
        }

        // --- susun daftar vendor dinamis dari kolom vendor1..vendor6 di header CS
        $vendors = [];
        for ($i = 1; $i <= 6; ++$i) {
            $idCol = "vendorid{$i}";
            $nameCol = "vendorname{$i}";
            if (!filled($cs->{$idCol}) && !filled($cs->{$nameCol})) {
                continue;
            }

            $vendors[] = [
                'idx' => $i,
                'id' => $cs->{$idCol},
                'name' => $cs->{$nameCol},
                'addr' => $cs->{"vendoralamat{$i}"} ?? null,
                'cp' => $cs->{"vendorcp{$i}"} ?? null,
                'telp' => $cs->{"vendortelp{$i}"} ?? null,
                'top' => $cs->{"vendortop{$i}"} ?? null,
                // ringkasan
                'total' => (float) ($cs->{"totalvendor{$i}"} ?? 0),
                'tax' => (float) ($cs->{"taxvendor{$i}"} ?? 0),
                'grand' => (float) ($cs->{"grandtotalvendor{$i}"} ?? 0),
                'grandselected' => (float) ($cs->{"grandtotalselectedvendor{$i}"} ?? 0),
            ];
        }
        $vendorCount = count($vendors); // dipakai view untuk lebar kolom dll.

        $data = [
            'title' => 'Canvass Sheet',
            'doc_type' => 'CS',
            'docid' => $cs->csid,
            'user_peminta' => ucwords(strtolower($cs->user_peminta)),
            'department_id' => $cs->department_id,
            'cpnyname' => optional($company)->cpny_name,
            'parent' => optional($company)->parent,
            'project' => optional($company)->project,
            'created_by_username' => $cs->created_by,
            'created_by_name' => ucwords(strtolower(optional($cs->creator)->name)),
            'created_at_fmt' => optional($cs->created_at)->format('d F Y'),
            'req_date_fmt' => optional($cs->created_at)->format('d M Y H:i'),
            'csdate' => \Carbon\Carbon::parse($cs->csdate)->format('d F Y'),
            'keperluan' => $cs->csnote ?? $cs->keperluan, // pilih yang tersedia
            'status_doc' => $status_doc,
            'requesttype_name' => optional($cs->requestType)->requesttype_name,
            'vendors' => $vendors,
            'vendorCount' => $vendorCount,
        ];

        $pdf = \PDF::loadView('pages.canvass.pdf_cs', array_merge($data, [
            'detail' => $csdetail,
            'approval' => $approval,
            'approve_count' => $approve_count,
        ]));

        // SELALU landscape (sesuai permintaan)
        $pdf->setPaper('A4', 'landscape');

        return $pdf->stream("pdf_cs_{$cs->csid}.pdf");
    }

    private function generatePOFromCS(TrCS $cs, $user, $potype): void
    {
        // Idempotent: kalau sudah ada PO untuk CS ini, jangan bikin lagi
        $already = TrPO::where('csid', $cs->csid)->exists();
        if ($already) {
            return;
        }

        $details = TrCSdetail::where('csid', $cs->csid)->get();
        if ($details->isEmpty()) {
            return;
        }

        // Kelompokkan baris per vendor terpilih
        $pickedByVendorIdx = collect([1, 2, 3, 4, 5, 6])->mapWithKeys(fn ($i) => [$i => collect()]);
        foreach ($details as $row) {
            for ($i = 1; $i <= 6; ++$i) {
                $sel = (bool) ($row->{"vendor{$i}selected"} ?? false);
                $vid = $row->{"vendorid{$i}"} ?? null;
                if ($sel && $vid) {
                    $pickedByVendorIdx[$i] = $pickedByVendorIdx[$i]->push($row);
                    break; // satu baris hanya 1 vendor terpilih
                }
            }
        }
        $nonEmptyGroups = $pickedByVendorIdx->filter(fn ($g) => $g->isNotEmpty());
        if ($nonEmptyGroups->isEmpty()) {
            return;
        }

        $now = Carbon::now();

        // Generator nomor 10 digit per company (tanpa prefix)
        $mkPonbr = function () use ($cs) {
            $company = strtoupper((string) $cs->cpny_id);
            $digits = 10;
            $base = ($company === 'GPS') ? 0 : 8000000000;

            $autonbr = Autonbr::lockForUpdate()
                ->where('doctype', $company)
                ->first();

            $current = $autonbr ? (int) $autonbr->number : (int) $base;
            $next = $current + 1;

            if (!$autonbr) {
                $autonbr = Autonbr::create([
                    'doctype' => $company,
                    'status' => 'A',
                    'number' => $next,
                ]);
            } else {
                $autonbr->update(['number' => $next]);
            }

            $ponbr = str_pad((string) $next, $digits, '0', STR_PAD_LEFT);

            return [$ponbr, $next];
        };

        DB::connection('pgsql')->beginTransaction();
        try {
            foreach ($nonEmptyGroups as $i => $rows) {
                // Info vendor dari header CS
                $vendorId = $cs->{"vendorid{$i}"} ?? null;
                $vendorName = $cs->{"vendorname{$i}"} ?? null;
                $vendorAddr = $cs->{"vendoralamat{$i}"} ?? null;
                $vendorTelp = $cs->{"vendortelp{$i}"} ?? null;
                $vendorCP = $cs->{"vendorcp{$i}"} ?? null;
                $vendorTOP = $cs->{"vendortop{$i}"} ?? null;
                $vendornote = $cs->{"vendornote{$i}"} ?? null;

                if (!$vendorId) {
                    continue;
                }

                // Pajak & tax code (dipakai untuk kalkulasi tiap detail)
                $ppnPct = (float) ($cs->{"ppnvendor{$i}"} ?? 0);
                $pphPct = (float) ($cs->{"pphvendor{$i}"} ?? 0);
                $taxCodeId = $cs->{"taxcodevendor{$i}"} ?? null;

                // Nomor PO
                [$ponbr, $poautonbr] = $mkPonbr();

                // ===== PO HEADER (sementara total=0, akan di-update setelah loop detail) =====
                $po = new TrPO();
                $po->setConnection('pgsql');

                $po->ponbr = $ponbr;
                $po->poautonbr = $poautonbr;
                $po->podate = $now->toDateString();
                $po->potype = $potype;
                $po->cpny_id = $cs->cpny_id;
                $po->csid = $cs->csid;
                $po->sppbjktid = $cs->sppbjktid;
                $po->department_id = $cs->department_id;
                $po->user_peminta = $cs->user_peminta;
                $po->keperluan = $cs->keperluan;
                $po->ponote = $cs->csnote;

                $po->vendorid = $vendorId;
                $po->vendorname = $vendorName;
                $po->vendoralamat = $vendorAddr;
                $po->vendortelp = $vendorTelp;
                $po->vendorcp = $vendorCP;
                $po->vendortop = $vendorTOP;
                $po->vendornote = $vendornote;

                // total akan dihitung dari detail:
                $po->totalamt = 0;
                $po->taxcodeid = $taxCodeId;
                $po->taxamt = 0;
                $po->grandtotalamt = 0;
                $po->totalqty = 0;
                $po->totalqtyreceived = 0;

                $po->submitdate = $now;
                $po->status = 'H';
                $po->created_by = $cs->created_by ?? 'system';
                $po->save();

                // ===== PO DETAIL =====
                $totalQty = 0.0;
                $sumTotal = 0.0; // jumlah totalcost detail
                $sumTax = 0.0; // jumlah tax detail
                $lineNo = 0;   // penomoran po_no per vendor

                foreach ($rows as $row) {
                    ++$lineNo;

                    $unitCost = (float) ($row->{"vendorprice{$i}"} ?? 0);
                    $totalCost = (float) ($row->{"vendortotalprice{$i}"} ?? 0);

                    // jika model pajak: tax = total * (PPN+PPH)
                    $lineTax = $totalCost * (($ppnPct + $pphPct) / 100);

                    $pd = new TrPOdetail();
                    $pd->ponbr = $ponbr;
                    $pd->po_no = $lineNo; // ← nomor urut 1,2,3,...

                    $pd->csid = $cs->csid;
                    $pd->cs_no = $row->cs_no ?? null;
                    $pd->sppbjktid = $row->sppbjktid ?? $cs->sppbjktid;
                    $pd->sppbjktid_no = $row->sppbjkt_no ?? null;

                    $pd->inventory_type = $row->inventory_type ?? null;
                    $pd->inventory_sub_type = $row->inventory_sub_type ?? null;
                    $pd->inventory_category = $row->inventory_category ?? null;
                    $pd->inventoryid = $row->inventoryid ?? null;
                    $pd->inventory_descr = $row->inventory_descr ?? null;
                    $pd->ponote_detail = $row->csnote_detail ?? null;

                    $pd->qty = (float) $row->qty;
                    $pd->uom = $row->uom;
                    $pd->siteid = $row->siteid ?? null;

                    $pd->type_multiplier = $row->type_multiplier ?? null;
                    $pd->base_multiplier = $row->base_multiplier ?? null;
                    $pd->base_qty = $row->base_qty ?? null;
                    $pd->base_uom = $row->base_uom ?? null;

                    $pd->unitcost = $unitCost;
                    $pd->taxcodeid = $taxCodeId;
                    $pd->taxamt = $lineTax;
                    $pd->totalcost = $totalCost;

                    $pd->qty_received = 0;
                    $pd->base_qty_received = 0;
                    $pd->qty_return = 0;
                    $pd->base_qty_return = 0;
                    $pd->qty_completed = 0;
                    $pd->base_qty_completed = 0;

                    $pd->received = false;
                    $pd->completed = false;
                    $pd->canceled = false;

                    $pd->budget_cpny_id = $row->budget_cpny_id ?? null;
                    $pd->budget_business_unit_id = $row->budget_business_unit_id ?? null;
                    $pd->budget_department_fin_id = $row->budget_department_fin_id ?? null;
                    $pd->budget_account_id = $row->budget_account_id ?? null;
                    $pd->budget_activity_id = $row->budget_activity_id ?? null;
                    $pd->budget_activity_descr = $row->budget_activity_descr ?? null;
                    $pd->budget_perpost = $row->budget_perpost ?? null;

                    $pd->status = 'H';
                    $pd->created_by =$cs->created_by ?? 'system';
                    $pd->save();

                    // === UPDATE TrCSdetail: set ponbr ===
                    $row->ponbr = $ponbr;
                    $row->updated_by =$cs->created_by ?? 'system'; // kalau ada field ini
                    $row->updated_at = now();                       // kalau pakai timestamps
                    $row->save();

                    $totalQty += (float) $row->qty;
                    $sumTotal += $totalCost;
                    $sumTax += $lineTax;
                }

                // ===== Update totals header dari akumulasi detail =====
                $po->totalqty = $totalQty;
                $po->totalamt = $sumTotal;
                $po->taxamt = $sumTax;
                $po->grandtotalamt = $sumTotal + $sumTax;
                $po->save();
            }

            DB::connection('pgsql')->commit();
        } catch (\Throwable $e) {
            DB::connection('pgsql')->rollBack();
            Log::error('Generate PO from CS failed', [
                'csid' => $cs->csid,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function validateSubmitServerSide(array $details): void
    {
        // Minimal 1 baris ada vendor selected kalau ada harga
        $hasAnyPrice = false;
        $everyPricedRowHasPick = true;

        foreach ($details as $row) {
            $rowHasPrice = false;
            $rowHasPick = false;
            foreach (($row['vendor'] ?? []) as $v) {
                $price = (float) ($v['price'] ?? 0);
                if ($price > 0) {
                    $rowHasPrice = true;
                    $hasAnyPrice = true;
                }
                if (!empty($v['selected'])) {
                    $rowHasPick = true;
                }
            }
            if ($rowHasPrice && !$rowHasPick) {
                $everyPricedRowHasPick = false;
                break;
            }
        }

        if (!$hasAnyPrice) {
            abort(422, 'Total tidak boleh 0. Isi harga minimal pada salah satu vendor.');
        }
        if (!$everyPricedRowHasPick) {
            abort(422, 'Ada baris yang memiliki harga tetapi belum memilih vendor.');
        }

        // Qty tidak boleh > qty kiriman (front-end sudah batasi; ini redundansi aman)
        foreach ($details as $row) {
            $qty = (float) ($row['qty'] ?? 0);
            if ($qty < 0) {
                abort(422, 'Qty tidak valid.');
            }
        }
    }

    private function buildSourceForDoc_xxx(string $doc, ?string $srcId): array
    {
        switch ($doc) {
            case 'SPPB':
                $h = TrSPPB::with(['requestType', 'creator', 'purchaser'])->findOrFail($srcId);
                $k = 'sppb_no';
                $d = TrSPPBdetail::where('sppbid', $h->sppbid)->orderBy($k)->get();
                break;
            case 'SPPJ':
                $h = TrSPPJ::with(['requestType', 'creator', 'purchaser'])->findOrFail($srcId);
                $k = 'sppj_no';
                $d = TrSPPJdetail::where('sppjid', $h->sppjid)->orderBy($k)->get();
                break;
            case 'SPPK':
                $h = TrSPPK::with(['requestType', 'creator', 'purchaser'])->findOrFail($srcId);
                $k = 'sppk_no';
                $d = TrSPPKdetail::where('sppkid', $h->sppkid)->orderBy($k)->get();
                break;
            case 'SPPT':
                $h = TrSPPT::with(['requestType', 'creator', 'purchaser'])->findOrFail($srcId);
                $k = 'sppt_no';
                $d = TrSPPTdetail::where('spptid', $h->spptid)->orderBy($k)->get();
                break;
            default: abort(422, 'Invalid doc type');
        }
        $idx = [];
        foreach ($d as $sd) {
            $key = strtoupper(trim($sd->inventoryid ?? '')).'|'.
                strtoupper(trim($sd->uom ?? '')).'|'.
                strtoupper(trim($sd->inventory_descr ?? ''));
            $idx[$key] = $sd;
        }

        return [$h, $d, $k, $idx];
    }

    private function buildSourceForDoc_zzz(string $doc, string $srcId): array
    {
        switch ($doc) {
            case 'SPPB':
                $h = TrSPPB::with(['requestType', 'creator', 'purchaser'])
                    ->where('sppbid', $srcId)
                    ->firstOrFail();

                $k = 'sppb_no';

                $d = TrSPPBdetail::on('pgsql')
                    ->where('sppbid', $h->sppbid)
                    ->orderBy($k)
                    ->get();
                break;

            case 'SPPJ':
                $h = TrSPPJ::with(['requestType', 'creator', 'purchaser'])
                    ->where('sppjid', $srcId)
                    ->firstOrFail();

                $k = 'sppj_no';

                $d = TrSPPJdetail::on('pgsql')
                    ->where('sppjid', $h->sppjid)
                    ->orderBy($k)
                    ->get();
                break;

            case 'SPPK':
                $h = TrSPPK::with(['requestType', 'creator', 'purchaser'])
                    ->where('sppkid', $srcId)
                    ->firstOrFail();

                $k = 'sppk_no';

                $d = TrSPPKdetail::on('pgsql')
                    ->where('sppkid', $h->sppkid)
                    ->orderBy($k)
                    ->get();
                break;

            case 'SPPT':
                $h = TrSPPT::with(['requestType', 'creator', 'purchaser'])
                    ->where('spptid', $srcId)
                    ->firstOrFail();

                $k = 'sppt_no';

                $d = TrSPPTdetail::on('pgsql')
                    ->where('spptid', $h->spptid)
                    ->orderBy($k)
                    ->get();
                break;

            default:
                abort(422, 'Invalid doc type');
        }

        // build index untuk matching
        $idx = [];
        foreach ($d as $sd) {
            $key = strtoupper(trim($sd->inventoryid ?? '')).'|'.
                strtoupper(trim($sd->uom ?? '')).'|'.
                strtoupper(trim($sd->inventory_descr ?? ''));
            $idx[$key] = $sd;
        }

        return [$h, $d, $k, $idx];
    }

    private function buildSourceForDoc(string $doc, string $srcId): array
    {
        $doc = strtoupper(trim($doc));
        $srcId = trim((string) $srcId);

        // Helper: cari header fleksibel (by doc key, by id numeric, by hashids->id)
        $findHeader = function (string $modelClass, string $docKey) use ($srcId) {
            // 1) coba by doc key (sppbid/sppjid/sppkid/spptid)
            $q = $modelClass::on('pgsql')->with(['requestType', 'creator', 'purchaser'])
                ->where($docKey, $srcId);

            $h = $q->first();
            if ($h) {
                return $h;
            }

            // 2) coba by numeric id
            if (ctype_digit($srcId)) {
                $h = $modelClass::on('pgsql')->with(['requestType', 'creator', 'purchaser'])
                    ->where('id', (int) $srcId)
                    ->first();
                if ($h) {
                    return $h;
                }
            }

            // 3) coba decode hashids -> id
            $decoded = Hashids::decode($srcId);
            $plainId = $decoded[0] ?? null;
            if ($plainId) {
                $h = $modelClass::on('pgsql')->with(['requestType', 'creator', 'purchaser'])
                    ->where('id', (int) $plainId)
                    ->first();
                if ($h) {
                    return $h;
                }
            }

            return null;
        };

        switch ($doc) {
            case 'SPPB':
                $h = $findHeader(TrSPPB::class, 'sppbid');
                if (!$h) {
                    throw new \Exception("SPPB not found for src_id={$srcId} (try send sppbid or eid)");
                }

                $k = 'sppb_no';

                $d = TrSPPBdetail::on('pgsql')
                    ->where('sppbid', $h->sppbid)
                    ->orderBy($k)
                    ->get();
                break;

            case 'SPPJ':
                $h = $findHeader(TrSPPJ::class, 'sppjid');
                if (!$h) {
                    throw new \Exception("SPPJ not found for src_id={$srcId} (try send sppjid or eid)");
                }

                $k = 'sppj_no';

                $d = TrSPPJdetail::on('pgsql')
                    ->where('sppjid', $h->sppjid)
                    ->orderBy($k)
                    ->get();
                break;

            case 'SPPK':
                $h = $findHeader(TrSPPK::class, 'sppkid');
                if (!$h) {
                    throw new \Exception("SPPK not found for src_id={$srcId} (try send sppkid or eid)");
                }

                $k = 'sppk_no';

                $d = TrSPPKdetail::on('pgsql')
                    ->where('sppkid', $h->sppkid)
                    ->orderBy($k)
                    ->get();
                break;

            case 'SPPT':
                $h = $findHeader(TrSPPT::class, 'spptid');
                if (!$h) {
                    throw new \Exception("SPPT not found for src_id={$srcId} (try send spptid or eid)");
                }

                $k = 'sppt_no';

                $d = TrSPPTdetail::on('pgsql')
                    ->where('spptid', $h->spptid)
                    ->orderBy($k)
                    ->get();
                break;

            default:
                abort(422, "Invalid doc type ({$doc})");
        }

        // build index untuk matching
        $idx = [];
        foreach ($d as $sd) {
            $key = strtoupper(trim($sd->inventoryid ?? '')).'|'.
                strtoupper(trim($sd->uom ?? '')).'|'.
                strtoupper(trim($sd->inventory_descr ?? ''));
            $idx[$key] = $sd;
        }

        return [$h, $d, $k, $idx];
    }

    private function updateOrderedOnSource(array $details, $srcHeader, $srcDetails, array $srcIndex, string $cpnyId): void
    {
        $addedTotalOrdered = 0.0;
        foreach ($details as $i => $d) {
            $hasPick = false;
            foreach (($d['vendor'] ?? []) as $v) {
                if (!empty($v['selected'])) {
                    $hasPick = true;
                    break;
                }
            }
            if (!$hasPick) {
                continue;
            }

            $orderedQty = (float) ($d['qty'] ?? 0);
            if ($orderedQty <= 0) {
                continue;
            }

            $key = strtoupper(trim($d['inventoryid'] ?? '')).'|'.
                strtoupper(trim($d['uom'] ?? '')).'|'.
                strtoupper(trim($d['inventory_descr'] ?? ''));
            $srcDet = $srcIndex[$key] ?? ($srcDetails[$i] ?? null);
            if (!$srcDet) {
                continue;
            }

            $detTable = $srcDet->getTable();
            if (Schema::connection('pgsql')->hasColumn($detTable, 'ordered')) {
                $srcDet->ordered = (float) ($srcDet->ordered ?? 0) + $orderedQty;
            }
            if (Schema::connection('pgsql')->hasColumn($detTable, 'openordered')) {
                $srcDet->openordered = max(0, (float) ($srcDet->openordered ?? 0) - $orderedQty);
            }
            $srcDet->save();

            $addedTotalOrdered += $orderedQty;
        }

        $hdrTable = $srcHeader->getTable();
        if (Schema::connection('pgsql')->hasColumn($hdrTable, 'totalordered')) {
            $srcHeader->totalordered = (float) ($srcHeader->totalordered ?? 0) + $addedTotalOrdered;
        }
        if (Schema::connection('pgsql')->hasColumn($hdrTable, 'totalopenordered')) {
            $srcHeader->totalopenordered = max(0, (float) ($srcHeader->totalopenordered ?? 0) - $addedTotalOrdered);
        }
        $srcHeader->save();
    }

    // Williem 251214 Reserve Budget
    private function reserveBudget(string $doctype, string $docid, string $cpnyId, string $activity, string $username): void
    {
        // Panggil PostgreSQL Stored Procedure: sp_process_budget(doctype, docid, activity, user)
        // Contoh: CALL sp_process_budget('CS','CS25120001','Submit','williemhalim');
        DB::connection('pgsql')->statement(
            'CALL public.sp_process_budget(?, ?, ?, ?,?)',
            [strtoupper($doctype), $docid,$cpnyId,$activity, $username]
        );
    }

    private function updateOrderedOnPOReuse(array $details, string $prevCsid, string $cpnyId): void
    {
        // Ambil semua baris reuse yang terkait CS sebelumnya (CS awal)
        $reuseRows = TrPOReuse::on('pgsql')
            ->where('csid', $prevCsid)
            ->when($cpnyId, function ($q) use ($cpnyId) {
                return $q->where('cpny_id', $cpnyId);
            })
            ->get();

        if ($reuseRows->isEmpty()) {
            return;
        }

        // Build index inventory | uom | descr -> row (boleh banyak, ambil pertama saja)
        $reuseIndex = [];
        foreach ($reuseRows as $row) {
            $key = strtoupper(trim($row->inventoryid)).'|'.
                strtoupper(trim($row->uom)).'|'.
                strtoupper(trim($row->inventory_descr));
            $reuseIndex[$key][] = $row;
        }

        $addedTotalPerPonbr = [];

        foreach ($details as $i => $d) {
            // cek apakah ada vendor yang dipilih
            $hasPick = false;
            foreach (($d['vendor'] ?? []) as $v) {
                if (!empty($v['selected'])) {
                    $hasPick = true;
                    break;
                }
            }
            if (!$hasPick) {
                continue;
            }

            $orderedQty = (float) ($d['qty'] ?? 0);
            if ($orderedQty <= 0) {
                continue;
            }

            $key = strtoupper(trim($d['inventoryid'] ?? '')).'|'.
                strtoupper(trim($d['uom'] ?? '')).'|'.
                strtoupper(trim($d['inventory_descr'] ?? ''));

            /** @var TrPOReuse|null $reuseDet */
            $reuseDetList = $reuseIndex[$key] ?? null;
            if (!$reuseDetList || count($reuseDetList) === 0) {
                continue;
            }

            // Ambil row pertama yang cocok
            $reuseDet = $reuseDetList[0];

            // Update ordered/openordered di reuse
            $reuseDet->ordered = (float) ($reuseDet->ordered ?? 0) + $orderedQty;
            $reuseDet->openordered = max(0, (float) ($reuseDet->openordered ?? 0) - $orderedQty);
            $reuseDet->updated_by = auth()->user()->username ?? $reuseDet->updated_by;
            $reuseDet->save();

            // Simpan total per PO (ponbr) untuk update header PO
            $ponbr = $reuseDet->ponbr;
            $addedTotalPerPonbr[$ponbr] = ($addedTotalPerPonbr[$ponbr] ?? 0) + $orderedQty;
        }

        // // Update header PO (totalordered / totalopenordered) jika kolom tersedia
        // if (!empty($addedTotalPerPonbr)) {
        //     $poList = TrPO::on('pgsql')
        //         ->whereIn('ponbr', array_keys($addedTotalPerPonbr))
        //         ->get();

        //     foreach ($poList as $po) {
        //         $delta = $addedTotalPerPonbr[$po->ponbr] ?? 0;
        //         if ($delta <= 0) continue;

        //         $conn    = $po->getConnectionName() ?? 'pgsql';
        //         $hdrTable = $po->getTable();

        //         if (Schema::connection($conn)->hasColumn($hdrTable, 'totalordered')) {
        //             $po->totalordered = (float)($po->totalordered ?? 0) + $delta;
        //         }
        //         if (Schema::connection($conn)->hasColumn($hdrTable, 'totalopenordered')) {
        //             $po->totalopenordered = max(
        //                 0,
        //                 (float)($po->totalopenordered ?? 0) - $delta
        //             );
        //         }

        //         $po->save();
        //     }
        // }
    }

    public function updateCoaCS(Request $request)
    {
        $rows = $request->input('rows', []);

        if (!is_array($rows) || empty($rows)) {
            return response()->json([
                'success' => false,
                'message' => 'No rows data provided.',
            ], 422);
        }

        $user = Auth::user();
        $username = $user->username ?? 'system';

        try {
            DB::transaction(function () use ($rows, $username) {
                foreach ($rows as $row) {
                    $id = $row['id'] ?? null;
                    $acc = $row['budget_account_id'] ?? null;

                    if (!$id) {
                        continue;
                    }

                    /** @var TrCSdetail|null $csd */
                    $csd = TrCSdetail::find($id);
                    if (!$csd) {
                        continue;
                    }

                    // Default: kosongkan dulu activity kalau COA kosong
                    $csd->budget_account_id = $acc;
                    $csd->budget_activity_id = null;
                    $csd->budget_activity_descr = null;

                    if (!empty($acc)) {
                        // Coba cari di BudgetDetail berdasarkan cpny + dept + perpost + account_id
                        $bd = BudgetDetail::query()
                            ->where('cpny_id', $csd->cpny_id)
                            ->where('department_fin_id', $csd->budget_depatment_fin_id) // atau budget_department_fin_id, sesuaikan fieldnya
                            ->where('perpost', $csd->perpost)
                            ->where('account_id', $acc)
                            ->first();

                        if ($bd) {
                            $csd->budget_activity_id = $bd->activity_id;
                            $csd->budget_activity_descr = $bd->activity_descr;
                        }
                    }

                    $csd->updated_by = $username;
                    $csd->save();
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'COA updated successfully.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update COA: '.$e->getMessage(),
            ], 500);
        }
    }

    private function updateRejectOrderedOnSource(TrCS $cs, string $username): void
    {
        try {
            // 0) basic sanity
            if (empty($cs->sppbjktid)) {
                return;
            }

            $prefix = strtoupper(substr((string) $cs->sppbjktid, 0, 2));
            $docType = null;

            if ($prefix === 'PB') {
                $docType = 'SPPB';
            } elseif ($prefix === 'PJ') {
                $docType = 'SPPJ';
            } elseif ($prefix === 'PK') {
                $docType = 'SPPK';
            } elseif ($prefix === 'PT') {
                $docType = 'SPPT';
            }

            if (!$docType) {
                return; // bukan dari SPPB/J/K/T
            }

            if (!$docType) {
                return;
            }

            \Log::info('[CS Reject] updateRejectOrderedOnSource start', [
                'csid' => $cs->csid,
                'sppbjktid' => $cs->sppbjktid,
                'docType' => $docType,
            ]);

            // 1) ambil source pakai builder
            [$srcHeader, $srcDetails, $srcLineKey, $srcIndex] = $this->buildSourceForDoc($docType, $cs->sppbjktid);

            // ✅ pastikan header pakai koneksi pgsql
            if (method_exists($srcHeader, 'setConnection')) {
                $srcHeader->setConnection('pgsql');
            }

            // 2) ambil detail CS
            $csDetails = TrCSdetail::on('pgsql')
                ->where('csid', $cs->csid)
                ->orderBy('cs_no')
                ->get();

            \Log::info('[CS Reject] CS details loaded', [
                'csid' => $cs->csid,
                'count' => $csDetails->count(),
            ]);

            $addedReject = 0.0;
            $updatedLines = 0;
            $missingMatch = 0;

            foreach ($csDetails as $i => $cd) {
                // hanya item yang ada vendor selected
                $hasPick = false;
                for ($slot = 1; $slot <= 6; ++$slot) {
                    if (!empty($cd->{"vendor{$slot}selected"})) {
                        $hasPick = true;
                        break;
                    }
                }
                if (!$hasPick) {
                    continue;
                }

                $qty = (float) ($cd->qty ?? 0);
                if ($qty <= 0) {
                    continue;
                }

                $key = strtoupper(trim((string) ($cd->inventoryid ?? ''))).'|'.
                    strtoupper(trim((string) ($cd->uom ?? ''))).'|'.
                    strtoupper(trim((string) ($cd->inventory_descr ?? '')));

                $srcDet = $srcIndex[$key] ?? null;

                // fallback berdasarkan cs_no mapping (lebih aman daripada $i)
                if (!$srcDet && !empty($cd->sppbjkt_no) && !empty($srcLineKey)) {
                    $srcDet = $srcDetails->firstWhere($srcLineKey, $cd->sppbjkt_no);
                }

                // fallback terakhir: index by position
                if (!$srcDet) {
                    $srcDet = $srcDetails[$i] ?? null;
                }

                if (!$srcDet) {
                    ++$missingMatch;
                    \Log::warning('[CS Reject] source detail not found', [
                        'csid' => $cs->csid,
                        'cs_no' => $cd->cs_no,
                        'key' => $key,
                        'sppbjkt_no' => $cd->sppbjkt_no ?? null,
                    ]);
                    continue;
                }

                // ✅ pastikan source detail pakai koneksi pgsql
                if (method_exists($srcDet, 'setConnection')) {
                    $srcDet->setConnection('pgsql');
                }

                $detTable = $srcDet->getTable();

                if (\Schema::connection('pgsql')->hasColumn($detTable, 'rejectordered')) {
                    $srcDet->rejectordered = (float) ($srcDet->rejectordered ?? 0) + $qty;
                }

                if (\Schema::connection('pgsql')->hasColumn($detTable, 'updated_by')) {
                    $srcDet->updated_by = $username;
                }

                $srcDet->save();

                $addedReject += $qty;
                ++$updatedLines;
            }

            // header.totalrejectordered += addedReject
            $hdrTable = $srcHeader->getTable();

            if (\Schema::connection('pgsql')->hasColumn($hdrTable, 'totalrejectordered')) {
                $srcHeader->totalrejectordered = (float) ($srcHeader->totalrejectordered ?? 0) + $addedReject;
            }
            if (\Schema::connection('pgsql')->hasColumn($hdrTable, 'updated_by')) {
                $srcHeader->updated_by = $username;
            }
            $srcHeader->save();

            \Log::info('[CS Reject] updateRejectOrderedOnSource done', [
                'csid' => $cs->csid,
                'addedReject' => $addedReject,
                'updatedLines' => $updatedLines,
                'missingMatch' => $missingMatch,
            ]);
        } catch (\Throwable $e) {
            \Log::error('[CS Reject] updateRejectOrderedOnSource ERROR', [
                'csid' => $cs->csid ?? null,
                'msg' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                // stack trace kepanjangan, tapi ini cukup buat pinpoint
            ]);
            throw $e; // biar transaksi/handler luar bisa rollback
        }
    }

    private function rollbackOrderedOnSourceForRevise(TrCS $cs, string $username): void
    {
        // Tentukan doc sumber dari prefix sppbjktid
        $prefix = strtoupper(substr((string) $cs->sppbjktid, 0, 2));
        $docType = null;

        if ($prefix === 'PB') {
            $docType = 'SPPB';
        } elseif ($prefix === 'PJ') {
            $docType = 'SPPJ';
        } elseif ($prefix === 'PK') {
            $docType = 'SPPK';
        } elseif ($prefix === 'PT') {
            $docType = 'SPPT';
        } else {
            return;
        } // bukan dari SPPB/J/K/T → skip

        // Ambil header + detail sumber
        [$srcHeader, $srcDetails, $srcLineKey, $srcIndex] = $this->buildSourceForDoc($docType, $cs->sppbjktid);

        // Ambil detail CS dari DB
        $csDetails = TrCSdetail::on('pgsql')
            ->where('csid', $cs->csid)
            ->orderBy('cs_no')
            ->get();

        $rolledBackTotal = 0.0;

        foreach ($csDetails as $i => $cd) {
            // hanya yang vendor selected
            $hasPick = false;
            for ($slot = 1; $slot <= 6; ++$slot) {
                if (!empty($cd->{"vendor{$slot}selected"})) {
                    $hasPick = true;
                    break;
                }
            }
            if (!$hasPick) {
                continue;
            }

            $qty = (float) ($cd->qty ?? 0);
            if ($qty <= 0) {
                continue;
            }

            $key = strtoupper(trim((string) ($cd->inventoryid ?? ''))).'|'.
                strtoupper(trim((string) ($cd->uom ?? ''))).'|'.
                strtoupper(trim((string) ($cd->inventory_descr ?? '')));

            $srcDet = $srcIndex[$key] ?? ($srcDetails[$i] ?? null);
            if (!$srcDet) {
                continue;
            }

            $detTable = $srcDet->getTable();

            // ✅ ordered -= qty
            if (\Schema::connection('pgsql')->hasColumn($detTable, 'ordered')) {
                $srcDet->ordered = max(0, (float) ($srcDet->ordered ?? 0) - $qty);
            }

            // ✅ openordered += qty
            if (\Schema::connection('pgsql')->hasColumn($detTable, 'openordered')) {
                $srcDet->openordered = (float) ($srcDet->openordered ?? 0) + $qty;
            }

            if (\Schema::connection('pgsql')->hasColumn($detTable, 'updated_by')) {
                $srcDet->updated_by = $username;
            }

            $srcDet->save();
            $rolledBackTotal += $qty;
        }

        // Header rollback
        $hdrTable = $srcHeader->getTable();

        if (\Schema::connection('pgsql')->hasColumn($hdrTable, 'totalordered')) {
            $srcHeader->totalordered = max(0, (float) ($srcHeader->totalordered ?? 0) - $rolledBackTotal);
        }

        if (\Schema::connection('pgsql')->hasColumn($hdrTable, 'totalopenordered')) {
            $srcHeader->totalopenordered = (float) ($srcHeader->totalopenordered ?? 0) + $rolledBackTotal;
        }

        if (\Schema::connection('pgsql')->hasColumn($hdrTable, 'updated_by')) {
            $srcHeader->updated_by = $username;
        }

        $srcHeader->save();
    }

    public function getLastPriceHistory(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        $inventoryid = trim((string) $request->query('inventoryid', ''));
        $csdateRaw = trim((string) $request->query('csdate', ''));

        if ($inventoryid === '') {
            return response()->json(['ok' => false, 'message' => 'inventoryid is required'], 422);
        }

        // parse csdate (optional)
        $csDate = null;
        if ($csdateRaw !== '') {
            try {
                // kalau csdate dari view format Y-m-d => aman
                $csDate = \Carbon\Carbon::parse($csdateRaw)->startOfDay();
            } catch (\Throwable $e) {
                // kalau format aneh, jangan bikin error query
                return response()->json(['ok' => false, 'message' => 'Invalid csdate format'], 422);
            }
        }

        // 1) Ambil history (dari TrPoLastPrice)
        $hist = TrPoLastPrice::query()
            ->select(['ponbr', 'podate', 'vendorname', 'inventory_descr', 'unitcost', 'csid', 'purchaser'])
            ->where('inventoryid', $inventoryid)
            ->whereNull('deleted_at')
            ->when($csDate, function ($q) use ($csDate) {
                // kalau podate tipe date: gunakan whereDate
                // kalau podate tipe timestamp: tetap aman pakai where('podate','<',$csDate)
                $q->whereDate('podate', '<', $csDate->toDateString());
            })
            ->orderByDesc('podate')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        // 2) Ambil mapping PO ID dari TrPO berdasarkan ponbr
        $ponbrs = $hist->pluck('ponbr')->filter()->unique()->values()->all();

        $poIdByPonbr = TrPO::query()
            ->select(['id', 'ponbr'])
            ->whereIn('ponbr', $ponbrs)
            ->pluck('id', 'ponbr'); // [ponbr => id]

        // 3) Bentuk response
        $rows = $hist->map(function ($r) use ($poIdByPonbr) {
            $poId = $poIdByPonbr[$r->ponbr] ?? null;

            return [
                'ponbr' => $r->ponbr,
                'eid' => $poId ? Hashids::encode($poId) : null,
                'podate' => $r->podate ? \Carbon\Carbon::parse($r->podate)->format('d/m/Y') : null,
                'csid' => $r->csid,
                'vendorname' => $r->vendorname,
                'inventory_descr' => $r->inventory_descr,
                'unitcost' => (float) ($r->unitcost ?? 0),
                'purchaser' => $r->purchaser,
            ];
        });

        return response()->json([
            'ok' => true,
            'data' => $rows,
        ]);
    }

    public function getLastPriceHistory_xxx(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        $inventoryid = $request->query('inventoryid');
        $csdate = $request->query('csdate');
        if (!$inventoryid) {
            return response()->json(['ok' => false, 'message' => 'inventoryid is required'], 422);
        }

        // 1) Ambil history (dari TrPoLastPrice)
        $hist = TrPoLastPrice::query()
            ->select(['ponbr', 'podate', 'vendorname', 'inventory_descr', 'unitcost', 'csid', 'purchaser'])
            ->where('podate', '<', $csdate)
            ->where('inventoryid', $inventoryid)
            ->whereNull('deleted_at')
            ->orderByDesc('podate')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        // 2) Ambil mapping PO ID dari TrPo berdasarkan ponbr
        $ponbrs = $hist->pluck('ponbr')->filter()->unique()->values()->all();

        // sesuaikan model TrPO kamu (nama class bisa TrPO / TrPo)
        $poIdByPonbr = TrPO::query()
            ->select(['id', 'ponbr'])
            ->whereIn('ponbr', $ponbrs)
            ->pluck('id', 'ponbr'); // [ponbr => id]

        // 3) Bentuk response
        $rows = $hist->map(function ($r) use ($poIdByPonbr) {
            $poId = $poIdByPonbr[$r->ponbr] ?? null;

            return [
                'ponbr' => $r->ponbr,
                'eid' => $poId ? Hashids::encode($poId) : null, // ✅ dari TrPo.id
                'podate' => $r->podate ? \Carbon\Carbon::parse($r->podate)->format('d/m/Y') : null,
                'csid' => $r->csid,
                'vendorname' => $r->vendorname,
                'inventory_descr' => $r->inventory_descr,
                'unitcost' => (float) ($r->unitcost ?? 0),
                'purchaser' => $r->purchaser,
            ];
        });

        return response()->json([
            'ok' => true,
            'data' => $rows,
        ]);
    }

    public function getLastPriceHistoryEntry(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        $inventoryid = $request->query('inventoryid');
        if (!$inventoryid) {
            return response()->json(['ok' => false, 'message' => 'inventoryid is required'], 422);
        }

        $rows = TrPoLastPrice::query()
            ->select(['ponbr', 'podate', 'vendorname', 'inventory_descr', 'unitcost', 'csid', 'purchaser'])
            ->where('inventoryid', $inventoryid)
            ->whereNull('deleted_at')
            ->orderByDesc('podate')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get()
            ->map(function ($r) {
                return [
                    'ponbr' => $r->ponbr,
                    'podate' => $r->podate ? \Carbon\Carbon::parse($r->podate)->format('d/m/Y') : null,
                    'csid' => $r->csid,
                    'vendorname' => $r->vendorname,
                    'inventory_descr' => $r->inventory_descr,
                    'unitcost' => (float) ($r->unitcost ?? 0),
                    'purchaser' => $r->purchaser,
                ];
            });

        return response()->json([
            'ok' => true,
            'data' => $rows,
        ]);
    }

    public function printBQCS($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $authUser = Auth::user();
        if (!$authUser) {
            return redirect()->route('login');
        }

        // Ambil SPPJ + relasi yang dibutuhkan
        $bq = Bq::findOrFail($id);

        // Detail baris SPPJ
        $bqdetail = BqDetail::where('bqid', $bq->bqid)
            ->get();

        $sppt = TrSPPT::where('spptid', $bq->sppjtid)
            ->first();

        $company = MsCompany::where('cpny_id', $bq->cpny_id)->first();

        $data = [
            'title' => 'Bills of Quantities (BQ)',
            'doc_type' => 'BQ',
            'cpny_id' => $company->cpny_id,
            'cpny_name' => $company->cpny_name,
            'keperluan' => $sppt->keperluan,
        ];

        // Kirim ke view
        $pdf = \PDF::loadView(
            'pages.sppts.pdfbq_sppt',
            array_merge($data, [
                'bq' => $bq,
                'bqdetail' => $bqdetail,
            ])
        );

        // Portrait jika <= 5 approver, else landscape
        $pdf->setPaper('A4');

        return $pdf->stream("pdfbq_sppt_{$bq->bqid}.pdf");
    }

    private function applyFastApproveForCS(string $csid, string $username, \Carbon\Carbon $dt): bool
    {
        // 1) ambil semua TOP yang fast approve
        $fastTopIds = MsTop::query()
            ->where('is_fastapprove', true)
            ->where('status', 'A')
            ->pluck('topid')
            ->filter()
            ->values()
            ->all();

        if (empty($fastTopIds)) {
            return false; // tidak ada TOP fastapprove => stop
        }

        // 2) ambil CS header untuk mapping vendorid -> vendortop
        $cs = TrCS::query()
            ->where('csid', $csid)
            ->first([
                'csid',
                'vendorid1', 'vendortop1',
                'vendorid2', 'vendortop2',
                'vendorid3', 'vendortop3',
                'vendorid4', 'vendortop4',
                'vendorid5', 'vendortop5',
                'vendorid6', 'vendortop6',
            ]);

        if (!$cs) {
            return false;
        }

        $vendorTopMap = [];
        for ($i = 1; $i <= 6; ++$i) {
            $vid = $cs->{"vendorid{$i}"} ?? null;
            $top = $cs->{"vendortop{$i}"} ?? null;

            if ($vid && $top) {
                $vendorTopMap[(string) $vid] = (string) $top;
            }
        }

        // 3) ambil semua detail & kumpulkan vendor yang selected (vendor1selected..6)
        $details = TrCSdetail::query()
            ->where('csid', $csid)
            ->get([
                'vendorid1', 'vendor1selected',
                'vendorid2', 'vendor2selected',
                'vendorid3', 'vendor3selected',
                'vendorid4', 'vendor4selected',
                'vendorid5', 'vendor5selected',
                'vendorid6', 'vendor6selected',
            ]);

        if ($details->isEmpty()) {
            return false;
        }

        $selectedVendorIds = [];
        foreach ($details as $row) {
            for ($i = 1; $i <= 6; ++$i) {
                $isSel = (bool) ($row->{"vendor{$i}selected"} ?? false);
                if ($isSel) {
                    $vid = $row->{"vendorid{$i}"} ?? null;
                    if ($vid) {
                        $selectedVendorIds[] = (string) $vid;
                    }
                }
            }
        }

        $selectedVendorIds = array_values(array_unique($selectedVendorIds));
        if (empty($selectedVendorIds)) {
            return false; // tidak ada selected vendor => stop
        }

        // 4) validasi: semua selected vendor harus punya TOP yang sama
        $selectedTopIds = [];
        foreach ($selectedVendorIds as $vid) {
            $topId = $vendorTopMap[$vid] ?? null;
            if (!$topId) {
                // vendor selected tapi TOP tidak ketemu di header => dianggap gagal
                return false;
            }
            $selectedTopIds[] = (string) $topId;
        }

        $selectedTopIds = array_values(array_unique($selectedTopIds));
        if (count($selectedTopIds) !== 1) {
            return false; // TOP vendor selected tidak sama => stop
        }

        $topIdFinal = $selectedTopIds[0];

        // 5) TOP final harus termasuk TOP yang fastapprove
        if (!in_array($topIdFinal, $fastTopIds, true)) {
            return false;
        }

        // 6) Kalau lolos -> update TrApproval status X untuk leveling >= 2
        $q = TrApproval::query()
            ->where('refnbr', $csid)
            ->where('aprv_leveling', '>=', 2);

        $payload = ['status' => 'X'];

        // optional columns (kalau ada)
        if (Schema::connection('pgsql')->hasColumn((new TrApproval())->getTable(), 'updated_by')) {
            $payload['updated_by'] = $username;
        }
        if (Schema::connection('pgsql')->hasColumn((new TrApproval())->getTable(), 'updated_at')) {
            $payload['updated_at'] = $dt;
        }

        $q->update($payload);

        return true;
    }

    private function monthToRoman(int $m): string
    {
        $map = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];

        return $map[$m] ?? '';
    }

    private function doctypeDescr(string $doctype): ?string
    {
        $map = [
            'KO' => 'KONTRAK',
            'SK' => 'No SK',
        ];

        $key = strtoupper(trim($doctype));

        return $map[$key] ?? null;
    }

    /**
     * Ambil nomor berikutnya dari ms_autonbr_test (pgsql2) dengan lockForUpdate.
     * - KO: reset per YEAR+MONTH (pad 4 digit)
     * - SK: reset per YEAR (pad 3 digit).
     */
    private function nextAutoNumber(string $doctype, int $year, int $month, int $pad): int
    {
        $doctype = strtoupper(trim($doctype));
        $descr = $this->doctypeDescr($doctype);
        $user = auth()->user()->username ?? 'system';
        $month = (int) $month; // 1..12

        return DB::connection('pgsql2')->transaction(function () use ($doctype, $descr, $year, $month) {
            $row = Autonbr::on('pgsql2')
                ->where('doctype', $doctype)
                ->where('year', $year)
                ->where('month', $month)
                ->lockForUpdate()
                ->first();

            $next = ((int) ($row->number ?? 0)) + 1;

            if (!$row) {
                Autonbr::on('pgsql2')->create([
                    'doctype' => $doctype,
                    'doctype_descr' => $descr,
                    'year' => $year,
                    'month' => $month,
                    'number' => $next,
                    'status' => 'A',
                    'created_by' => 'system',
                    'updated_by' => 'system',
                ]);
            } else {
                $row->update([
                    'number' => $next,
                    'updated_by' => 'system',
                    'doctype_descr' => $row->doctype_descr ?: $descr,
                ]);
            }

            return $next;
        });
    }

    private function makeKontrakId(Carbon $now): string
    {
        $yy = $now->format('y');
        $mm = $now->format('m');

        $seq = $this->nextAutoNumber('KO', (int) $now->format('Y'), (int) $now->format('n'), 4);

        return 'KO'.$yy.$mm.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    private function makeNoSk(string $cpnyId, Carbon $now): string
    {
        // contoh format (aku rapikan dikit): 024/SK/AW/II/2025
        $roman = $this->monthToRoman((int) $now->format('n'));
        $year = $now->format('Y');

        // ✅ running 3 digit per BULAN
        $seq = $this->nextAutoNumber('SK', (int) $now->format('Y'), (int) $now->format('n'), 3);

        return str_pad((string) $seq, 3, '0', STR_PAD_LEFT)
            .'/PROC'
            .'/'.strtoupper(trim($cpnyId))
            .'/SK'
            .'/'.$roman
            .'/'.$year;
    }

    private function generateKontrakFromCS(TrCS $cs, $user): void
    {
        // Idempotent: kalau sudah ada kontrak untuk CS ini, jangan bikin lagi
        $already = TrKontrak::where('csid', $cs->csid)->exists();
        if ($already) {
            return;
        }

        $details = TrCSdetail::where('csid', $cs->csid)->get();
        if ($details->isEmpty()) {
            return;
        }

        // Kelompokkan baris per vendor terpilih (vendor1..vendor6)
        $pickedByVendorIdx = collect([1, 2, 3, 4, 5, 6])->mapWithKeys(fn ($i) => [$i => collect()]);
        foreach ($details as $row) {
            for ($i = 1; $i <= 6; ++$i) {
                $sel = (bool) ($row->{"vendor{$i}selected"} ?? false);
                $vid = $row->{"vendorid{$i}"} ?? null;
                if ($sel && $vid) {
                    $pickedByVendorIdx[$i] = $pickedByVendorIdx[$i]->push($row);
                    break; // 1 baris hanya 1 vendor terpilih
                }
            }
        }

        $nonEmptyGroups = $pickedByVendorIdx->filter(fn ($g) => $g->isNotEmpty());
        if ($nonEmptyGroups->isEmpty()) {
            return;
        }

        $u_approval = TrApproval::where('refnbr', $cs->sppbjktid)
            ->where('aprv_leveling', '1.00')
            ->first();
        $user_approval = $u_approval->aprv_username ?? null;

        // $now   = Carbon::now();
        $now = Carbon::create(2026, 2, 5, 10, 0, 0);
        // dd($now);
        $cpny = strtoupper((string) ($cs->cpny_id ?? $cs->cpnyid ?? ''));

        DB::connection('pgsql')->beginTransaction();
        DB::connection('pgsql2')->beginTransaction(); // autonbr ada di pgsql2
        try {
            foreach ($nonEmptyGroups as $i => $rows) {
                // info vendor dari header CS (mengikuti pola generatePOFromCS)
                $vendorId = $cs->{"vendorid{$i}"} ?? null;
                $vendorName = $cs->{"vendorname{$i}"} ?? null;

                if (!$vendorId) {
                    continue;
                }

                // Generate nomor
                $kontrakId = $this->makeKontrakId($now);     // KO26010001
                $noSk = $this->makeNoSk($cpny, $now);   // SK/024/AW/X/2025

                // ===== KONTRAK HEADER =====
                $k = new TrKontrak();
                $k->setConnection('pgsql');

                $k->kontrakid = $kontrakId;
                $k->kontrakdate = $now->toDateString();
                $k->cpny_id = $cpny;

                $k->csid = $cs->csid;
                $k->sppbjktid = $cs->sppbjktid ?? null;

                $k->department_id = $cs->department_id ?? $cs->departementid ?? null;
                $k->user_peminta = $cs->user_peminta ?? null;
                $k->user_approval = $user_approval ?? null;
                $k->purchaser = $cs->created_by ?? null;

                $k->keperluan = $cs->keperluan ?? null;

                $k->vendorid = $vendorId;
                $k->vendorname = $vendorName;

                // Sesuaikan kalau ada field spesifik kontrak di CS
                $k->kontraktype = null;
                $k->kontrakcategory = $cs->bqcategory ?? $cs->kontrakcategory ?? null;

                $k->nosk = $noSk;
                $k->nopklegal = $cs->nopklegal ?? null;

                $k->startdate = $cs->startdate ?? null;
                $k->enddate = $cs->enddate ?? null;

                $k->kontaknote = null;

                $k->submitdate = null;
                $k->status = 'H';

                $k->created_by = $cs->created_by ?? ($user->username ?? 'system');
                $k->save();

                /*
                 * OPTIONAL:
                 * kalau kamu punya field di TrCSdetail untuk simpan kontrakid (misal: kontrakid),
                 * kamu bisa update disini per baris $rows.
                 *
                 * Contoh:
                 * foreach ($rows as $row) { $row->kontrakid = $kontrakId; $row->save(); }
                 */
            }

            DB::connection('pgsql2')->commit();
            DB::connection('pgsql')->commit();
        } catch (\Throwable $e) {
            DB::connection('pgsql2')->rollBack();
            DB::connection('pgsql')->rollBack();

            Log::error('Generate Kontrak from CS failed', [
                'csid' => $cs->csid,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
