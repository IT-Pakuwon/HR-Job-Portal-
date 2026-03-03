<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Str;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use App\Models\TrKontrak;
use App\Models\TrAttachment;
use App\Models\TrCS;
use App\Models\TrSPPB;
use App\Models\TrSPPJ;
use App\Models\TrSPPK;
use App\Models\TrSPPT;
use App\Models\SysUserRole;
use Illuminate\Validation\Rule;
use App\Models\MsKontrakCategory;
use App\Models\TrBQCSDetail;
use App\Models\MsVendor;
use App\Models\TrCSdetail;
use App\Models\TrPOReuse;

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
            $statusClass = 'bg-gray-200/60 text-gray-700 border border-gray-500/40';

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

        $users = \App\Models\User::select('username','name')
            ->where('status', 'A') // kalau ada
            ->orderBy('username')
            ->get();

        $kontrakCategories = $this->getKontrakCategories();

         // ===== Detail Kontrak (TrBQCSDetail) =====
        $detailQ = TrBQCSDetail::query();

        // Paling aman: filter by bqid + csid kalau tersedia
        if (!empty($kontrak->bqid)) {
            $detailQ->where('bqid', $kontrak->bqid);
        }

        if (!empty($kontrak->csid)) {
            $detailQ->where('csid', $kontrak->csid);
        }

        // Fallback kalau ternyata kontrak tidak punya bqid/csid
        // (sesuaikan jika di data kamu relasinya pakai kontrak_bq_id)
        if (empty($kontrak->bqid) && empty($kontrak->csid)) {
            $detailQ->where('kontrak_bq_id', $kontrak->kontrakid);
        }

        $details = $detailQ
            ->orderBy('bq_no')
            ->orderBy('bq_line_no')
            ->get();

        return view('pages.kontrak.createkontrak', [
            'kontrak'    => $kontrak,
            'attachment' => $attachment,
            'hash'       => $hash,
            'sppbUrl'    => $sppbUrl,
            'csUrl'      => $csUrl,
            'users'      => $users,
            'kontrakCategories' => $kontrakCategories,
            'details'    => $details,
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

        $users = \App\Models\User::select('username','name')
            ->where('status', 'A') // kalau ada
            ->orderBy('username')
            ->get();

        $kontrakCategories = $this->getKontrakCategories();

        // ===== Detail Kontrak (TrBQCSDetail) =====
        $detailQ = TrBQCSDetail::query();

        // Paling aman: filter by bqid + csid kalau tersedia
        if (!empty($kontrak->bqid)) {
            $detailQ->where('bqid', $kontrak->bqid);
        }

        if (!empty($kontrak->csid)) {
            $detailQ->where('csid', $kontrak->csid);
        }

        // Fallback kalau ternyata kontrak tidak punya bqid/csid
        // (sesuaikan jika di data kamu relasinya pakai kontrak_bq_id)
        if (empty($kontrak->bqid) && empty($kontrak->csid)) {
            $detailQ->where('kontrak_bq_id', $kontrak->kontrakid);
        }

        $details = $detailQ
            ->orderBy('bq_no')
            ->orderBy('bq_line_no')
            ->get();

        // // ===== Kumpulkan semua vendorid dari detail =====
        // $vendorIds = collect($details)
        //     ->flatMap(function ($d) {
        //         return [
        //             $d->vendorid1,
        //             $d->vendorid2,
        //             $d->vendorid3,
        //             $d->vendorid4,
        //             $d->vendorid5,
        //             $d->vendorid6,
        //         ];
        //     })
        //     ->filter() // buang null / kosong
        //     ->unique()
        //     ->values();

        // // ===== Ambil nama vendor sekali query =====
        // $vendorMap = [];
        // if ($vendorIds->isNotEmpty()) {
        //     $vendorMap = MsVendor::query()
        //         ->whereIn('vendor_id', $vendorIds)
        //         ->pluck('vendor_name', 'vendor_id')
        //         ->toArray();
        // }

        return view('pages.kontrak.showkontrak', [
            'kontrak'    => $kontrak,
            'attachment' => $attachment,
            'hash'       => $hash,
            'sppbUrl'    => $sppbUrl,
            'csUrl'      => $csUrl,
            'users'      => $users,
            'kontrakCategories' => $kontrakCategories,
            'details'    => $details,
            // 'vendorMap'  => $vendorMap,
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
            'kontrakcategory' => [
                'required',
                'string',
                'max:100',
                function ($attribute, $value, $fail) {
                    $ok = MsKontrakCategory::query()
                        ->where('status', 'A')
                        ->where('kontrakcategory', $value)
                        ->exists();

                    if (!$ok) {
                        $fail('Kontrak Category tidak valid / tidak aktif.');
                    }
                },
            ],
            'kontrakdate'     => 'required|date',
            'startdate'       => 'required|date',
            'enddate'         => 'required|date|after_or_equal:startdate',           
            'kontraknote'     => 'nullable|string|max:5000',
            'nosk'            => 'nullable|string|max:255',
        ], [
            'enddate.after_or_equal' => 'End Date harus >= Start Date.',
            'user_approval.required' => 'User Approval wajib dipilih.',
            'kontrakcategory.exists' => 'Kontrak Category tidak valid / tidak aktif.',
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
        $kontrak->nosk            = $request->nosk;
        // $kontrak->user_approval   = $request->user_approval; // ⬅️ simpan ini
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

    public function editKontrak(string $eid)
    {
        $user = auth()->user();
        abort_if(!$user, 401);

        $id = \Vinkla\Hashids\Facades\Hashids::decode($eid)[0] ?? null;
        abort_if(!$id, 404);

        $kontrak = TrKontrak::findOrFail($id);

        // dd($kontrak);

        // hanya owner boleh edit
        $createdBy = strtolower((string)($kontrak->created_by ?? ''));
        $username  = strtolower((string)($user->username ?? ''));

        abort_if($createdBy !== $username, 403, 'Forbidden');

        // 👉 SET STATUS KE HOLD
        if ($kontrak->status !== 'H') {
            $kontrak->status     = 'H';
            $kontrak->updated_by = $user->username;
            $kontrak->updated_at = now();
            $kontrak->save();
        }

        // redirect ke halaman create/edit
        return redirect()->to('/createkontrak/' . $eid);
    }

    private function getKontrakCategories()
    {
        return MsKontrakCategory::query()
            ->where('status', 'A') // kalau status aktif kamu beda, ganti di sini
            ->orderBy('kontrakcategory')
            ->get(['kontrakcategory', 'kontrakcategory_descr']);
    }

    private function isOwnerKontrak(TrKontrak $kontrak, $user): bool
    {
        if (!$user) return false;

        $createdBy = (string)($kontrak->created_by ?? '');
        $u1 = strtolower($user->username ?? '');
        $u2 = strtolower($user->name ?? '');
        $u3 = strtolower($user->email ?? '');

        $cb = strtolower($createdBy);
        return $cb !== '' && ($cb === $u1 || $cb === $u2 || $cb === $u3);
    }

    public function terminate(string $eid, Request $request)
    {
        $id = Hashids::decode($eid)[0] ?? null;
        abort_if(!$id, 404);

        $user = $request->user();
        abort_if(!$user, 403);

        $kontrak = TrKontrak::query()->findOrFail($id);

        abort_if(!$this->isOwnerKontrak($kontrak, $user), 403);

        // optional: batasi status yang boleh di-terminate
        // abort_if(in_array(strtoupper((string)$kontrak->status), ['C'], true), 422);

        $username = $user->username ?? $user->name ?? 'system';

        $kontrak->status     = 'T';
        $kontrak->updated_by = $username;
        $kontrak->updated_at = now();
        $kontrak->save();

        return back()->with('success', 'Kontrak berhasil di-Terminate (status T).');
    }

    public function reuse(string $eid, Request $request)
    {
        $id = Hashids::decode($eid)[0] ?? null;
        abort_if(!$id, 404);

        $user = $request->user();
        abort_if(!$user, 403);

        $kontrak = TrKontrak::query()->findOrFail($id);

        abort_if(!$this->isOwnerKontrak($kontrak, $user), 403);

        $username = $user->username ?? $user->name ?? 'system';

        DB::transaction(function () use ($kontrak, $username) {
            // 1) ambil detail CS
            $csid = (string)($kontrak->csid ?? '');
            if ($csid === '') {
                abort(422, 'CS ID kosong. Tidak bisa Reuse.');
            }

            $csDetails = TrCSdetail::query()
                ->where('csid', $csid)
                ->get();

            if ($csDetails->isEmpty()) {
                abort(422, "Tidak ada TrCSdetail untuk CS ID: {$csid}");
            }

            TrPOReuse::where('ponbr', $kontrak->kontrakid)->where('csid', $kontrak->csid)->delete();
            // 2) insert ke TrPOReuse
            foreach ($csDetails as $d) {
                TrPOReuse::create([
                    'cpny_id' => $kontrak->cpny_id,

                    // ✅ ponbr diisi kontrakid (sesuai request kamu)
                    'ponbr'   => $kontrak->kontrakid,
                    'po_no'   => null,

                    'csid'      => $kontrak->csid,
                    'sppbjktid' => $kontrak->sppbjktid,
                    'cs_no'     => $d->cs_no,
                    'sppbjkt_no'=> $d->sppbjkt_no,

                    'inventory_type'      => $d->inventory_type,
                    'inventory_sub_type'  => $d->inventory_sub_type,
                    'inventory_category'  => $d->inventory_category,
                    'inventoryid'         => $d->inventoryid,
                    'inventory_descr'     => $d->inventory_descr,

                    'qty'       => $d->qty,
                    'uom'       => $d->uom,
                    'siteid'    => $d->siteid,

                    'type_multiplier' => $d->type_multiplier,
                    'base_multiplier' => $d->base_multiplier,
                    'base_qty'        => $d->base_qty,
                    'base_uom'        => $d->base_uom,

                    'budget_perpost'           => $d->budget_perpost,
                    'budget_cpny_id'           => $d->budget_cpny_id,
                    'budget_business_unit_id'  => $d->budget_business_unit_id,
                    'budget_department_fin_id' => $d->budget_department_fin_id,
                    'budget_account_id'        => $d->budget_account_id,
                    'budget_activity_id'       => $d->budget_activity_id,
                    'budget_activity_descr'    => $d->budget_activity_descr,

                    // default qty tracking
                    'openordered'       => $d->qty, // atau base_qty, sesuai logika kamu
                    'ordered'           => 0,
                    'rejectordered'     => 0,
                    'completeordered'   => 0,

                    'status'     => 'D', // karena reuse
                    'created_by' => $username,
                    'created_at' => now(),
                    'updated_by' => $username,
                    'updated_at' => now(),
                ]);
            }

            // 3) update status kontrak -> D
            $kontrak->status     = 'D';
            $kontrak->updated_by = $username;
            $kontrak->updated_at = now();
            $kontrak->save();
        });

        return back()->with('success', 'Kontrak berhasil di-Reuse (status D) dan TrPOReuse berhasil diinsert.');
    }


}
