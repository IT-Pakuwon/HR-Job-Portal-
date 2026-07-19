<?php

namespace App\Http\Controllers;

use App\Exports\TrainingAttendanceExport;
use App\Http\Controllers\Traits\HasAttendanceWindow;
use App\Models\MsCompany;
use App\Models\MsDepartment;
use App\Models\StoGrading;
use App\Models\TrTrainingCertificate;
use App\Models\TrTrainingRegistration;
use App\Models\TrTrainingScheduleDetail;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;

class TrainingAttendanceController extends Controller
{
    use HasAttendanceWindow;

    public function index()
    {
        return view('pages.training_attendance.index');
    }

    /**
     * Selectable "events" — one row per non-draft schedule date, newest
     * first. Not restricted to dates that already have an Approved
     * registrant — HR needs to be able to open an event and see "no one's
     * approved yet" rather than have it silently missing from the list.
     */
    public function events()
    {
        $details = TrTrainingScheduleDetail::whereIn('status', ['PUBLISHED', 'CLOSED'])
            ->withCount(['registrations as approved_count' => fn ($q) => $q->approved()])
            ->with('schedule.training')
            ->orderByDesc('schedule_date')
            ->get();

        $gradeIds = $details->pluck('schedule.grade_id')->filter()->unique();
        $gradeNames = $gradeIds->isEmpty()
            ? collect()
            : StoGrading::whereIn('grade_id', $gradeIds)->pluck('grade_name', 'grade_id');

        $rows = $details->map(function ($d) use ($gradeNames) {
            $schedule = $d->schedule;
            $training = $schedule?->training;

            return [
                'id' => $d->id,
                'docid' => $d->docid,
                'training_name' => $training->training_name ?? null,
                'grade_name' => $gradeNames[$schedule->grade_id ?? null] ?? ($schedule->grade_id ?? null),
                'schedule_date' => $d->schedule_date,
                'start_time' => $d->start_time,
                'end_time' => $d->end_time,
                'mode' => $d->mode,
                'location' => $d->location,
                'platform' => $d->platform,
                'status' => $d->status,
                'approved_count' => $d->approved_count,
                'is_locked' => (bool) $d->attendance_locked_at,
            ];
        })->values();

        return response()->json(['data' => $rows]);
    }

    private function decorateRegistrations($registrations)
    {
        $usernames = $registrations->pluck('username')->unique();
        $names = $usernames->isEmpty() ? collect() : User::whereIn('username', $usernames)->pluck('name', 'username');

        $cpnyIds = $registrations->pluck('cpny_id')->filter()->unique();
        $cpnyNames = $cpnyIds->isEmpty() ? collect() : MsCompany::whereIn('cpny_id', $cpnyIds)->pluck('cpny_name', 'cpny_id');

        $deptIds = $registrations->pluck('department_id')->filter()->unique();
        $deptNames = $deptIds->isEmpty() ? collect() : MsDepartment::whereIn('department_id', $deptIds)->pluck('department_name', 'department_id');

        return $registrations->map(fn ($r) => [
            'id' => $r->id,
            'docid' => $r->docid,
            'username' => $r->username,
            'name' => $names[$r->username] ?? $r->username,
            'cpny_name' => $cpnyNames[$r->cpny_id] ?? $r->cpny_id,
            'department_name' => $deptNames[$r->department_id] ?? $r->department_id,
            'attended_at' => $r->attended_at,
            'attended_by' => $r->attended_by,
        ])->values();
    }

    public function roster($scheduleDetailId)
    {
        $detail = TrTrainingScheduleDetail::findOrFail($scheduleDetailId);

        $registrations = TrTrainingRegistration::where('schedule_detail_id', $scheduleDetailId)
            ->approved()
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'data' => $this->decorateRegistrations($registrations),
            'is_locked' => (bool) $detail->attendance_locked_at,
        ]);
    }

    public function scan(Request $request)
    {
        $request->validate([
            'schedule_detail_id' => 'required|integer',
            'code' => 'required|string',
        ]);

        $detail = TrTrainingScheduleDetail::findOrFail($request->schedule_detail_id);

        $registration = TrTrainingRegistration::where('attendance_code', trim($request->code))
            ->approved()
            ->first();

        if (!$registration) {
            return response()->json(['success' => false, 'message' => 'Barcode tidak dikenali'], 404);
        }

        if ((int) $registration->schedule_detail_id !== (int) $detail->id) {
            return response()->json(['success' => false, 'message' => 'Barcode ini bukan untuk event yang dipilih'], 422);
        }

        if (!$this->isWithinAttendanceWindow($detail)) {
            return response()->json(['success' => false, 'message' => 'Barcode belum berlaku atau sudah kedaluwarsa'], 422);
        }

        return response()->json([
            'success' => true,
            'data' => $this->decorateRegistrations(collect([$registration]))->first(),
            'is_locked' => (bool) $detail->attendance_locked_at,
        ]);
    }

    public function markAttend($registrationId)
    {
        $registration = TrTrainingRegistration::findOrFail($registrationId);
        $detail = TrTrainingScheduleDetail::findOrFail($registration->schedule_detail_id);

        if ($detail->attendance_locked_at) {
            return response()->json(['success' => false, 'message' => 'Attendance untuk event ini sudah dikunci'], 422);
        }

        if (!$registration->attended_at) {
            $user = Auth::user();

            $registration->update([
                'attended_at' => now(),
                'attended_by' => $user->username ?? 'system',
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $this->decorateRegistrations(collect([$registration->fresh()]))->first(),
        ]);
    }

    public function lock($scheduleDetailId)
    {
        $detail = TrTrainingScheduleDetail::findOrFail($scheduleDetailId);

        if ($detail->attendance_locked_at) {
            return response()->json(['success' => false, 'message' => 'Attendance sudah dikunci sebelumnya'], 422);
        }

        $user = Auth::user();

        $detail->update([
            'attendance_locked_at' => now(),
            'attendance_locked_by' => $user->username ?? 'system',
        ]);

        return response()->json(['success' => true, 'message' => 'Attendance berhasil dikunci']);
    }

    public function afterEvent($scheduleDetailId)
    {
        $registrations = TrTrainingRegistration::where('schedule_detail_id', $scheduleDetailId)
            ->approved()
            ->whereNotNull('attended_at')
            ->orderBy('attended_at')
            ->get();

        $decorated = $this->decorateRegistrations($registrations);

        $canUpload = (bool) Auth::user()?->hasRole('HCDEVACCESS');

        $certsByRegistration = TrTrainingCertificate::whereIn('registration_id', $registrations->pluck('id'))
            ->get()
            ->groupBy('registration_id');

        $decorated = $decorated->map(function ($row) use ($certsByRegistration, $canUpload) {
            $certs = $certsByRegistration->get($row['id'], collect());
            $row['certificate_count'] = $certs->count();
            $row['certificates'] = $certs->map(fn ($c) => [
                'id' => $c->id,
                'original_name' => $c->original_name,
                'url' => asset($c->file_path),
            ])->values();

            return $row;
        });

        return response()->json([
            'data' => $decorated,
            'can_upload' => $canUpload,
        ]);
    }

    public function uploadCertificate(Request $request, $registrationId)
    {
        $user = Auth::user();
        abort_unless($user && $user->hasRole('HCDEVACCESS'), 403);

        $registration = TrTrainingRegistration::findOrFail($registrationId);

        $request->validate([
            'files' => 'required|array|min:1',
            'files.*' => 'file|mimes:jpg,jpeg,pdf|max:5120',
        ]);

        $existing = TrTrainingCertificate::where('registration_id', $registration->id)->count();
        $incoming = count($request->file('files'));

        if ($existing + $incoming > 5) {
            return response()->json([
                'success' => false,
                'message' => "Maksimal 5 file per peserta (sudah ada {$existing})",
            ], 422);
        }

        $year = now()->year;
        $folder = public_path("training_certificates/{$year}/{$registration->id}");
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        foreach ($request->file('files') as $file) {
            $filename = md5(random_int(10000000, 99999999)) . '-' . str_replace('%', '', $file->getClientOriginalName());
            $file->move($folder, $filename);

            TrTrainingCertificate::create([
                'registration_id' => $registration->id,
                'file_path' => "training_certificates/{$year}/{$registration->id}/{$filename}",
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'size_bytes' => $file->getSize(),
                'uploaded_by' => $user->username,
                'uploaded_at' => now(),
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Sertifikat berhasil diupload']);
    }

    public function exportExcel($scheduleDetailId)
    {
        return Excel::download(new TrainingAttendanceExport((int) $scheduleDetailId), 'attendance.xlsx');
    }

    public function exportCsv($scheduleDetailId)
    {
        return Excel::download(new TrainingAttendanceExport((int) $scheduleDetailId), 'attendance.csv', ExcelFormat::CSV);
    }

    public function exportPdf($scheduleDetailId)
    {
        $detail = TrTrainingScheduleDetail::with('schedule.training')->findOrFail($scheduleDetailId);

        $registrations = TrTrainingRegistration::where('schedule_detail_id', $scheduleDetailId)
            ->approved()
            ->whereNotNull('attended_at')
            ->orderBy('attended_at')
            ->get();

        $pdf = Pdf::loadView('pages.training_attendance.export-pdf', [
            'rows' => $this->decorateRegistrations($registrations),
            'trainingName' => $detail->schedule?->training->training_name ?? '-',
            'scheduleDate' => $detail->schedule_date,
        ]);

        return $pdf->download('attendance.pdf');
    }
}
