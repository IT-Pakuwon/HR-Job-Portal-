<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Vinkla\Hashids\Facades\Hashids;

use App\Models\TrTrainingRegistration;
use App\Models\MsTraining;
use App\Models\User;
use App\Http\Controllers\ApprovalController;

class TrainingRegistrationController extends Controller
{
    protected $doctype = 'TR';

    /*
    |--------------------------------------------------------------------------
    | INDEX PAGE
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $user = Auth::user();

        $all = TrTrainingRegistration::where('employee_id', $user->id)->count();

        $pending = TrTrainingRegistration::where('employee_id', $user->id)
                    ->where('status','P')->count();

        $approved = TrTrainingRegistration::where('employee_id', $user->id)
                    ->where('status','C')->count();

        $rejected = TrTrainingRegistration::where('employee_id', $user->id)
                    ->where('status','R')->count();

        $revise = TrTrainingRegistration::where('employee_id', $user->id)
                    ->where('status','D')->count();

        return view('pages.training.registration.index',
            compact('all','pending','approved','rejected','revise'));
    }

    /*
    |--------------------------------------------------------------------------
    | JSON (DATATABLES)
    |--------------------------------------------------------------------------
    */
    public function json(Request $request)
    {
        $user = Auth::user();

        $data = TrTrainingRegistration::with(['training'])
                ->where('employee_id', $user->id)
                ->orderBy('created_at','desc')
                ->get();

        $data->transform(function($row){
            $row->eid = Hashids::encode($row->id);
            return $row;
        });

        return response()->json([
            'data' => $data
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | STORE REGISTRATION
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            'training_id'  => 'required',
            'level_detail' => 'required'
        ]);

        $user = Auth::user();
        $dt   = Carbon::now();

        DB::beginTransaction();

        try {

            $reg = new TrTrainingRegistration();
            $reg->training_id  = $request->training_id;
            $reg->employee_id  = $user->id;
            $reg->level_detail = $request->level_detail;
            $reg->status       = 'P';
            $reg->created_by   = $user->username;
            $reg->save();

            /*
            |--------------------------------------------------------------------------
            | GENERATE APPROVAL
            |--------------------------------------------------------------------------
            */
            $approvalCtl = app(ApprovalController::class);

            [$firstApprover, $lines] = $approvalCtl->generateForDocument(
                $reg->id,
                $this->doctype,
                $user->cpny_id,
                $user->department_id,
                $user->username,
                ['ignore_nominal'=>true],
                $dt
            );

            if ($firstApprover) {
                $reg->current_approver = $firstApprover;
                $reg->save();
            }

            $eid = Hashids::encode($reg->id);

            $approvalCtl->notifyFirstApprover(
                $reg->id,
                $this->doctype,
                'P',
                'Training Registration',
                url('/training-registration/show/'.$eid),
                [
                    'info'      => 'Training Registration',
                    'createdby' => $user->username,
                    'date'      => $dt->toDateTimeString(),
                ]
            );

            DB::commit();

            return response()->json([
                'success'=>true,
                'message'=>'Registration submitted successfully'
            ]);

        } catch(\Throwable $e){

            DB::rollBack();

            return response()->json([
                'success'=>false,
                'message'=>'Registration failed',
                'error'=>$e->getMessage()
            ],500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */
    public function show($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id,404);

        $registration = TrTrainingRegistration::with([
                'training',
                'employee'
            ])->findOrFail($id);

        return view('pages.training.registration.show',
            compact('registration','hash'));
    }

    /*
    |--------------------------------------------------------------------------
    | APPROVE
    |--------------------------------------------------------------------------
    */
    public function approve(Request $request, $id)
    {
        $user = $request->user();
        $registration = TrTrainingRegistration::findOrFail($id);

        $approvalCtl = app(ApprovalController::class);

        $result = $approvalCtl->approveStep(
            $registration->id,
            $this->doctype,
            $user->username,
            $user->name,

            // COMPLETE CALLBACK
            function($refnbr,$now) use ($registration){

                $registration->status = 'C';
                $registration->approved_by = auth()->user()->username;
                $registration->approved_at = $now;
                $registration->save();
            },

            // NEXT APPROVER CALLBACK
            function($next,$now) use ($registration){

                $registration->current_approver = $next->aprv_username;
                $registration->save();
            }
        );

        if(!$result['ok']){
            return response()->json([
                'success'=>false,
                'message'=>$result['message']
            ],403);
        }

        return response()->json([
            'success'=>true,
            'message'=>'Approved successfully'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | REJECT
    |--------------------------------------------------------------------------
    */
    public function reject(Request $request, $id)
    {
        $user = $request->user();
        $registration = TrTrainingRegistration::findOrFail($id);

        $approvalCtl = app(ApprovalController::class);

        $result = $approvalCtl->rejectStep(
            $registration->id,
            $this->doctype,
            $user->username,
            $user->name,

            function($refnbr,$now) use ($registration){

                $registration->status = 'R';
                $registration->approved_by = auth()->user()->username;
                $registration->approved_at = $now;
                $registration->save();
            }
        );

        if(!$result['ok']){
            return response()->json([
                'success'=>false,
                'message'=>$result['message']
            ],403);
        }

        return response()->json([
            'success'=>true,
            'message'=>'Rejected successfully'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | REVISE
    |--------------------------------------------------------------------------
    */
    public function revise(Request $request, $id)
    {
        $user = $request->user();
        $registration = TrTrainingRegistration::findOrFail($id);

        $approvalCtl = app(ApprovalController::class);

        $result = $approvalCtl->reviseStep(
            $registration->id,
            $this->doctype,
            $user->username,
            $user->name,

            function($refnbr,$now) use ($registration){

                $registration->status = 'D';
                $registration->approved_by = auth()->user()->username;
                $registration->approved_at = $now;
                $registration->save();
            }
        );

        if(!$result['ok']){
            return response()->json([
                'success'=>false,
                'message'=>$result['message']
            ],403);
        }

        return response()->json([
            'success'=>true,
            'message'=>'Revision requested'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CANCEL
    |--------------------------------------------------------------------------
    */
    public function cancel($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id,404);

        $registration = TrTrainingRegistration::findOrFail($id);

        $registration->status = 'X';
        $registration->updated_by = Auth::user()->username;
        $registration->save();

        return response()->json([
            'success'=>true,
            'message'=>'Registration cancelled'
        ]);
    }
}
