<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Str;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

use App\Models\TrKontrak;
use App\Models\TrAttachment;
use App\Models\TrCS;
use App\Models\TrSPPB;
use App\Models\TrSPPJ;
use App\Models\TrSPPK;
use App\Models\TrSPPT;
use App\Models\SysUserRole;

class KontrakController extends Controller
{
    /** Halaman index */
    public function index()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $u = $user->username ?? '';

        $cpnyRaw  = $user->cpny_id ?? '';
        $cpnyList = $cpnyRaw !== '' ? array_values(array_filter(array_map('trim', explode(',', $cpnyRaw)))) : [];

        $isFinanceAccess = SysUserRole::where('username', $u)
            ->where('role_id', 'FINACCESS')
            ->exists();

        return view('pages.kontrak.kontrak', [
            'companies'       => $cpnyList,
            'isFinanceAccess' => $isFinanceAccess,
            'loginUser'       => $u,
        ]);
    }

    /** DataTables server-side */
    public function json(Request $req)
    {
        $user = Auth::user();
        $u    = $user->username ?? '';

        $tab     = strtolower((string) $req->query('tab', 'my'));         // my | all
        $status  = strtoupper(trim((string) $req->query('status', '')));  // optional
        $company = strtoupper(trim((string) $req->query('company', ''))); // optional
        $creator = trim((string) $req->query('creator', ''));             // optional

        // company list user
        $cpnyRaw  = $user->cpny_id ?? '';
        $cpnyList = $cpnyRaw !== '' ? array_values(array_filter(array_map('trim', explode(',', $cpnyRaw)))) : [];

        $isFinanceAccess = SysUserRole::where('username', $u)
            ->where('role_id', 'FINACCESS')
            ->exists();

        $base = TrKontrak::query();

        // ===== filter company user (selalu) =====
        if (!empty($cpnyList)) {
            $base->whereIn('cpny_id', $cpnyList);
        }

        // ===== filter company dropdown (optional) =====
        if ($company !== '') {
            if (in_array($company, $cpnyList, true)) {
                $base->where('cpny_id', $company);
            }
        }

        // ===== tab behavior =====
        if ($tab === 'my') {
            // creator filter: non-fin selalu dirinya sendiri
            if (!$isFinanceAccess) {
                $base->where('created_by', $u);
            } else {
                // finance boleh filter creator (kalau diisi)
                if ($creator !== '') {
                    $base->where('created_by', $creator);
                }
            }

            // status filter (My saja)
            if ($status !== '') {
                // silakan sesuaikan status yang valid untuk kontrak di sistem kamu
                $allowed = ['H','P','O','C','X','D'];
                if (in_array($status, $allowed, true)) {
                    $base->where('status', $status);
                }
            }
        } else {
            // all: tidak filter creator
        }

        return $this->buildJsonTrKontrak($req, $base);
    }

    /** Builder hasil JSON */
    private function buildJsonTrKontrak(Request $req, $base)
    {
        $draw   = (int) $req->input('draw', 1);
        $start  = (int) $req->input('start', 0);
        $length = (int) $req->input('length', 25);
        $search = trim((string) $req->input('search.value', ''));

        $loginUser = Auth::user()->username ?? '';

        $t = (new TrKontrak)->getTable(); // "tr_kontrak"

        // urutan kolom sesuai tabel di view
        $columns = [
            0 => "$t.kontrakid",
            1 => "$t.kontrakdate",
            2 => "$t.cpny_id",
            3 => "$t.kontraktype",
            4 => "$t.kontrakcategory",
            5 => "$t.vendorname",
            6 => "$t.startdate",
            7 => "$t.enddate",
            8 => "$t.created_by",
            9 => "$t.status",
        ];

        $orderIdx = (int) $req->input('order.0.column', 1);
        $orderDir = $req->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $columns[$orderIdx] ?? "$t.kontrakdate";

        if ($search !== '') {
            $base->where(function ($q) use ($search, $t) {
                $q->where("$t.kontrakid", 'ilike', "%{$search}%")
                  ->orWhere("$t.vendorname", 'ilike', "%{$search}%")
                  ->orWhere("$t.created_by", 'ilike', "%{$search}%")
                  ->orWhereRaw("CAST($t.cpny_id AS TEXT) ILIKE ?", ["%{$search}%"])
                  ->orWhereRaw("CAST($t.kontraktype AS TEXT) ILIKE ?", ["%{$search}%"])
                  ->orWhereRaw("CAST($t.kontrakcategory AS TEXT) ILIKE ?", ["%{$search}%"])
                  ->orWhereRaw("TO_CHAR($t.kontrakdate,'YYYY-MM-DD') ILIKE ?", ["%{$search}%"])
                  ->orWhereRaw("TO_CHAR($t.startdate,'YYYY-MM-DD') ILIKE ?", ["%{$search}%"])
                  ->orWhereRaw("TO_CHAR($t.enddate,'YYYY-MM-DD') ILIKE ?", ["%{$search}%"]);
            });
        }

        $recordsTotal    = (clone $base)->count();
        $recordsFiltered = (clone $base)->count();

        $rows = $base->select(
            "$t.id",
            "$t.kontrakid",
            "$t.kontrakdate",
            "$t.cpny_id",
            "$t.kontraktype",
            "$t.kontrakcategory",
            "$t.vendorname",
            "$t.startdate",
            "$t.enddate",
            "$t.created_by",
            "$t.status"
        )
        ->orderBy($orderCol, $orderDir)
        ->orderBy("$t.kontrakid", 'desc')
        ->skip($start)->take($length)
        ->get();

        $rows->transform(function ($r) use ($loginUser) {
            $r->eid = Hashids::encode($r->id);

            $st = strtoupper((string)($r->status ?? ''));
            $statusText  = $st !== '' ? $st : 'Unknown';
            $statusClass = 'bg-gray-100 text-gray-700 border-gray-200';

            switch ($st) {
                case 'H':
                    $statusText  = 'Unsend';
                    $statusClass = 'bg-blue-100 text-blue-700 border-blue-200';
                    break;
                case 'P':
                    $statusText  = 'On Progress';
                    $statusClass = 'bg-yellow-100 text-yellow-700 dark:bg-yellow-800/30 dark:text-yellow-300 border-yellow-200';
                    break;
                case 'C':
                    $statusText  = 'Completed';
                    $statusClass = 'bg-emerald-100 text-emerald-700 border-emerald-200';
                    break;
            }

            $r->status_label = $statusText;
            $r->status_class = $statusClass;

            // ===== tambah flag owner =====
            $r->is_owner = strtolower((string)($r->created_by ?? '')) === strtolower((string)$loginUser);

            unset($r->id);
            return $r;
        });


        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $rows,
        ]);
    }

    public function createKontrak($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        // Header Kontrak
        $kontrak = TrKontrak::findOrFail($id);

        // -------- Ambil lampiran dari tr_attachment & buat Signed URL --------
        // IMPORTANT: pastikan refnbr attachment kontrak = kontrakid
        $rows = TrAttachment::where('refnbr', $kontrak->kontrakid)
            ->where('status', 'A')
            ->orderBy('created_at', 'desc')
            ->get();

        $config = config('filesystems.disks.gcs');
        $keyFilePath = $config['key_file'];
        if (!Str::startsWith($keyFilePath, ['/','C:\\','D:\\'])) {
            $keyFilePath = base_path($keyFilePath);
        }

        $storage = new StorageClient([
            'projectId'   => $config['project_id'],
            'keyFilePath' => $keyFilePath,
        ]);
        $bucket = $storage->bucket($config['bucket']);

        $attachment = $rows->map(function ($r) use ($bucket) {
            $objectPath = rtrim($r->folder, '/') . '/' . $r->filename;
            $object     = $bucket->object($objectPath);

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
                'id'           => $r->id,
                'display_name' => $r->attachment_name,
                'created_by'   => $r->created_by,
                'created_at'   => $r->created_at,
                'url'          => $signedUrl,
                'folder'       => $r->folder,
                'filename'     => $r->filename,
                'extention'    => $r->extention,
                'size'         => $r->filesize,
            ];
        });

        // ===== link SPPB/J/K/T =====
        $sppbUrl = null;
        if (!empty($kontrak->sppbjktid)) {
            $prefix = strtoupper(substr((string) $kontrak->sppbjktid, 0, 2));
            $srcId = null;

            if ($prefix === 'PB') {
                $srcId = TrSPPB::where('sppbid', $kontrak->sppbjktid)->value('id');
            } elseif ($prefix === 'PJ') {
                $srcId = TrSPPJ::where('sppjid', $kontrak->sppbjktid)->value('id');
            } elseif ($prefix === 'PK') {
                $srcId = TrSPPK::where('sppkid', $kontrak->sppbjktid)->value('id');
            } elseif ($prefix === 'PT') {
                $srcId = TrSPPT::where('spptid', $kontrak->sppbjktid)->value('id');
            }

            $routeMap = [
                'PB' => 'showsppbs',
                'PJ' => 'showsppjs',
                'PK' => 'showsppks',
                'PT' => 'showsppts',
            ];

            if ($srcId && isset($routeMap[$prefix])) {
                $sppbHash = Hashids::encode($srcId);
                $sppbUrl  = url("/{$routeMap[$prefix]}/{$sppbHash}");
            }
        }

        // ===== link CS =====
        $csUrl = null;
        if (!empty($kontrak->csid)) {
            $csId = TrCS::where('csid', $kontrak->csid)->value('id');
            if ($csId) {
                $csHash = Hashids::encode($csId);
                $csUrl  = url("/showcs/{$csHash}");
            }
        }

        return view('pages.kontrak.createkontrak', [
            'kontrak'    => $kontrak,
            'attachment' => $attachment,
            'hash'       => $hash,
            'sppbUrl'    => $sppbUrl,
            'csUrl'      => $csUrl,
        ]);
    }

    public function showKontrak($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        // Header Kontrak
        $kontrak = TrKontrak::findOrFail($id);

        // -------- Ambil lampiran dari tr_attachment & buat Signed URL --------
        // IMPORTANT: pastikan refnbr attachment kontrak = kontrakid
        $rows = TrAttachment::where('refnbr', $kontrak->kontrakid)
            ->where('status', 'A')
            ->orderBy('created_at', 'desc')
            ->get();

        $config = config('filesystems.disks.gcs');
        $keyFilePath = $config['key_file'];
        if (!Str::startsWith($keyFilePath, ['/','C:\\','D:\\'])) {
            $keyFilePath = base_path($keyFilePath);
        }

        $storage = new StorageClient([
            'projectId'   => $config['project_id'],
            'keyFilePath' => $keyFilePath,
        ]);
        $bucket = $storage->bucket($config['bucket']);

        $attachment = $rows->map(function ($r) use ($bucket) {
            $objectPath = rtrim($r->folder, '/') . '/' . $r->filename;
            $object     = $bucket->object($objectPath);

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
                'id'           => $r->id,
                'display_name' => $r->attachment_name,
                'created_by'   => $r->created_by,
                'created_at'   => $r->created_at,
                'url'          => $signedUrl,
                'folder'       => $r->folder,
                'filename'     => $r->filename,
                'extention'    => $r->extention,
                'size'         => $r->filesize,
            ];
        });

        // ===== link SPPB/J/K/T =====
        $sppbUrl = null;
        if (!empty($kontrak->sppbjktid)) {
            $prefix = strtoupper(substr((string) $kontrak->sppbjktid, 0, 2));
            $srcId = null;

            if ($prefix === 'PB') {
                $srcId = TrSPPB::where('sppbid', $kontrak->sppbjktid)->value('id');
            } elseif ($prefix === 'PJ') {
                $srcId = TrSPPJ::where('sppjid', $kontrak->sppbjktid)->value('id');
            } elseif ($prefix === 'PK') {
                $srcId = TrSPPK::where('sppkid', $kontrak->sppbjktid)->value('id');
            } elseif ($prefix === 'PT') {
                $srcId = TrSPPT::where('spptid', $kontrak->sppbjktid)->value('id');
            }

            $routeMap = [
                'PB' => 'showsppbs',
                'PJ' => 'showsppjs',
                'PK' => 'showsppks',
                'PT' => 'showsppts',
            ];

            if ($srcId && isset($routeMap[$prefix])) {
                $sppbHash = Hashids::encode($srcId);
                $sppbUrl  = url("/{$routeMap[$prefix]}/{$sppbHash}");
            }
        }

        // ===== link CS =====
        $csUrl = null;
        if (!empty($kontrak->csid)) {
            $csId = TrCS::where('csid', $kontrak->csid)->value('id');
            if ($csId) {
                $csHash = Hashids::encode($csId);
                $csUrl  = url("/showcs/{$csHash}");
            }
        }

        return view('pages.kontrak.showkontrak', [
            'kontrak'    => $kontrak,
            'attachment' => $attachment,
            'hash'       => $hash,
            'sppbUrl'    => $sppbUrl,
            'csUrl'      => $csUrl,
        ]);
    }

    public function submitKontrak(Request $request, string $kontrakid)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $kontrak = TrKontrak::where('kontrakid', $kontrakid)->first();
        if (!$kontrak) {
            return response()->json(['success' => false, 'message' => 'Kontrak not found'], 404);
        }

        // hanya bisa submit jika HOLD
        if (($kontrak->status ?? '') !== 'H') {
            return response()->json(['success' => false, 'message' => 'Dokumen hanya bisa di-Submit jika status = HOLD (H).'], 422);
        }

        // optional: hanya owner yang boleh submit
        $createdBy = $kontrak->created_by ?? null;
        if ($createdBy && strtolower((string)$createdBy) !== strtolower((string)($user->username ?? ''))) {
            return response()->json(['success' => false, 'message' => 'Anda tidak punya akses untuk submit dokumen ini.'], 403);
        }

        $v = Validator::make($request->all(), [
            'kontraktype'     => 'required|in:New,Adjustment',
            'kontrakcategory' => 'required|in:Maintenance,Pengadaan',
            'kontrakdate'     => 'required|date',
            'startdate'       => 'required|date',
            'enddate'         => 'required|date|after_or_equal:startdate',
            'kontraknote'     => 'nullable|string|max:5000',
        ], [
            'enddate.after_or_equal' => 'End Date harus >= Start Date.',
        ]);

        if ($v->fails()) {
            return response()->json([
                'success' => false,
                'message' => $v->errors()->first(),
                'errors'  => $v->errors(),
            ], 422);
        }

        $now = Carbon::now();

        $kontrak->kontraktype     = $request->kontraktype;
        $kontrak->kontrakcategory = $request->kontrakcategory;
        $kontrak->kontrakdate     = $request->kontrakdate;
        $kontrak->startdate       = $request->startdate;
        $kontrak->enddate         = $request->enddate;
        $kontrak->kontaknote      = $request->kontraknote;

        $kontrak->submitdate      = $now;
        $kontrak->status          = 'P';
        $kontrak->updated_by      = $user->username ?? null;
        $kontrak->updated_at      = $now;
        $kontrak->save();

        return response()->json([
            'success' => true,
            'message' => 'Submit berhasil.',
        ]);
    }
}
