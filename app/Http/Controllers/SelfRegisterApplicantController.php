<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\SelfPosting;
use App\Models\MsDivision;
use App\Models\MsDepartment;
use App\Models\Autonbr;
use App\Models\Applicant;
use App\Models\ApplicantCourse;
use App\Models\ApplicantEducation;
use App\Models\ApplicantFamily;
use App\Models\ApplicantLanguage;
use App\Models\ApplicantMarital;
use App\Models\ApplicantSW;
use App\Models\ApplicantSkill;
use App\Models\ApplicantWorking;
use Mail;
use Vinkla\Hashids\Facades\Hashids;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Support\Facades\Storage;

class SelfRegisterApplicantController extends Controller
{


    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Your session has expired. Please sign in again.'], 401)
                : redirect()->route('login')->with('error', 'Your session has expired. Please sign in again.');
        }

        $q = DB::connection('mysql3')->table('viewselfregister');

        $all      = (clone $q)->count();       

        return view('pages.selfregister.selfapplicant', compact('all'));
    }

    public function json(Request $request)
    {
        $status = $request->query('status'); // '', 'HP', 'R', 'C'

        $start    = (int) $request->input('start', 0);
        $length   = (int) $request->input('length', 10);
        $global   = trim((string) $request->input('search.value', ''));
        $orderIdx = (int) $request->input('order.0.column', 1);
        $orderDir = strtolower($request->input('order.0.dir', 'desc')) === 'asc' ? 'asc' : 'desc';

            $nameToDb = [
                'docid'          => 'vc.docid',
                'apply_date'     => 'vc.apply_date',
                'fullname'       => 'vc.fullname',
                'education_name' => 'vc.education_name',
                'religion'       => 'vc.religion',
                'height'         => 'vc.height',
                'weight'         => 'vc.weight',
                'company_name'   => 'vc.company_name',
                'status'         => 'vc.status',
            ];

        $base = DB::connection('mysql3')->table('viewselfregister as vc');

        // Filter status card
        if (!empty($status)) {
            if ($status === 'HP') $base->whereIn('vc.status', ['H','P']);
            else $base->where('vc.status', $status); // R / C / dll
        }

        $recordsTotal = (clone $base)->count();

        $query = (clone $base);

        // Global search
        if ($global !== '') {
            $like = "%{$global}%";
            $query->where(function ($q) use ($like) {
                $q->where('vc.docid', 'like', $like)
                ->orWhere('vc.fullname', 'like', $like)
                ->orWhere('vc.education_name', 'like', $like)
                ->orWhere('vc.religion', 'like', $like)
                ->orWhere('vc.company_name', 'like', $like)
                ->orWhere('vc.apply_date', 'like', $like)
                ->orWhere('vc.status', 'like', $like);
            });
        }

        // Per-column search
        $cols = $request->input('columns', []);
        foreach ($cols as $c) {
            $name = $c['name'] ?? null;
            $val  = isset($c['search']['value']) ? trim((string) $c['search']['value']) : '';
            if (!$name || $val === '') continue;

            $dbcol = $nameToDb[$name] ?? null;
            if (!$dbcol) continue;

            $query->where($dbcol, 'like', "%{$val}%");
        }

        $recordsFiltered = (clone $query)->count();

        // Sorting
        $orderName = $request->input("columns.$orderIdx.name");
        $orderBy   = $nameToDb[$orderName] ?? 'vc.docid';

        // Paging
        if ($length !== -1) {
            $query->skip($start)->take($length);
        }

        $rows = $query->select([
                'vc.id',
                'vc.docid',
                'vc.apply_date',
                'vc.fullname',
                'vc.education_name',
                'vc.religion',
                'vc.height',
                'vc.weight',
                'vc.company_name',
                'vc.status',
            ])
            ->orderBy($orderBy, $orderDir)
            ->get();

        $data = $rows->map(function ($r) {
            return [
                'eid'            => Hashids::encode($r->id),
                'docid'          => $r->docid,
                'apply_date'     => $r->apply_date,
                'fullname'       => $r->fullname,
                'education_name' => $r->education_name,
                'religion'       => $r->religion,
                'height'         => $r->height,
                'weight'         => $r->weight,
                'company_name'   => $r->company_name,
                'status'         => $r->status,
            ];
        });

        return response()->json([
            'draw'            => (int) $request->input('draw'),
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    public function showSelfRegister($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();       

        if (!$user) {
            return redirect()->route('login');
        }

        $career = SelfPosting::findOrFail($id);

        $applicant = Applicant::where('applicant_id', $career->applicant_id)->first();
        $applicant_family = ApplicantFamily::where('applicant_id', $career->applicant_id)->get();
        $applicant_marital = ApplicantMarital::where('applicant_id', $career->applicant_id)->get();
        $applicant_education = ApplicantEducation::where('applicant_id', $career->applicant_id)->get();
        $applicant_working = ApplicantWorking::where('applicant_id', $career->applicant_id)->get();
        $applicant_language = ApplicantLanguage::where('applicant_id', $career->applicant_id)->get();
        $applicant_course = ApplicantCourse::where('applicant_id', $career->applicant_id)->get();
        $applicant_sw = ApplicantSW::where('applicant_id', $career->applicant_id)->get();
        $applicant_skill = ApplicantSkill::where('applicant_id', $career->applicant_id)->get();

        $year = now()->year;       
        $config = config('filesystems.disks.gcs');
        // Pastikan StorageClient di-import dan digunakan dengan benar
        $storage = new StorageClient([
            'projectId'   => $config['project_id'],
            'keyFilePath' => $config['key_file'],
        ]);

        $bucket = $storage->bucket($config['bucket']);
        $expiration = \Carbon\Carbon::now()->addMinutes(30);

        $photo = null;
        $cv = null;
        $coverletter = null;

        if (!empty($applicant->upload_photo)) {
            $object = $bucket->object($applicant->upload_photo);
            // signedUrl expects DateTimeInterface
            $photo = $object->signedUrl($expiration);
        }

        if (!empty($applicant->upload_cv)) {
            $object = $bucket->object($applicant->upload_cv);
            $cv = $object->signedUrl($expiration);
        }

    
        return view('pages.selfregister.showapplicant', compact(
            'hash','career','applicant','applicant_family','applicant_marital','applicant_education','applicant_working','applicant_language','applicant_course','applicant_sw',
            'applicant_skill','year','photo','cv','coverletter'
        ));

    }

    

    

}
