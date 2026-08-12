<?php

namespace App\Http\Controllers;

use App\Models\MsCompany;
use App\Models\MsDepartment;
use App\Models\TrAttachment;
use App\Models\User;
use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Picqer\Barcode\BarcodeGeneratorPNG;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $talenta = User::where('username', $user->username)->first();
        [$originCpnyName, $originDepartmentName] = $this->resolveOriginNames($talenta);

        return view('profile.show', compact('talenta', 'originCpnyName', 'originDepartmentName'));
    }

    /**
     * PNG rendering of the caller's own personal check-in barcode (see
     * User::getBarcodeCodeAttribute()). No route parameter — always the
     * authenticated user's own code, so there's no id to guess/enumerate.
     * Meant for the HR attendance barcode scanner, which just needs the
     * plain code back.
     */
    public function barcodeImage()
    {
        $user = Auth::user();

        if (!$user) {
            abort(401);
        }

        $generator = new BarcodeGeneratorPNG();
        $png = $generator->getBarcode($user->barcode_code, $generator::TYPE_CODE_128, 2, 60);

        return response($png, 200)->header('Content-Type', 'image/png');
    }

    /**
     * QR twin of the barcode above, but encoding a vCard instead of the bare
     * code — so a phone camera app recognizes it as "Add Contact" (Name,
     * NPK-as-title, email, origin company/department) rather than just
     * showing raw text. The same check-in code the barcode carries is tucked
     * into a custom X-USERCODE field: invisible to a phone's contacts UI,
     * but TrainingAttendanceController::scan() looks for it so this QR
     * doubles as a valid check-in scan too.
     */
    public function qrImage()
    {
        $user = Auth::user();

        if (!$user) {
            abort(401);
        }

        $talenta = User::where('username', $user->username)->first();
        $vcard = $this->buildVcard($talenta);

        $renderer = new GDLibRenderer(240, 8, 'png');
        $writer = new Writer($renderer);
        $png = $writer->writeString($vcard);

        return response($png, 200)->header('Content-Type', 'image/png');
    }

    /**
     * @return array{0: ?string, 1: ?string} [origin company name, origin department name]
     */
    private function resolveOriginNames(User $user): array
    {
        $originCpnyName = $user->origin_cpny_id
            ? (MsCompany::where('cpny_id', $user->origin_cpny_id)->value('cpny_name') ?? $user->origin_cpny_id)
            : null;

        $originDepartmentName = $user->origin_department_id
            ? (MsDepartment::where('department_id', $user->origin_department_id)->value('department_name') ?? $user->origin_department_id)
            : null;

        return [$originCpnyName, $originDepartmentName];
    }

    private function buildVcard(User $user): string
    {
        [$originCpnyName, $originDepartmentName] = $this->resolveOriginNames($user);

        $escape = fn (string $v) => str_replace(["\\", ';', ','], ['\\\\', '\\;', '\\,'], $v);

        $lines = [
            'BEGIN:VCARD',
            'VERSION:3.0',
            'N:;' . $escape($user->name) . ';;;',
            'FN:' . $escape($user->name),
            'ORG:' . $escape($originCpnyName ?? ''),
            'TITLE:NPK ' . $escape($user->npk ?? '-'),
            'EMAIL;TYPE=INTERNET:' . $escape((string) $user->email),
        ];

        if (!empty($user->phonenumber)) {
            $lines[] = 'TEL;TYPE=WORK:' . $escape($user->phonenumber);
        }

        if ($originDepartmentName) {
            $lines[] = 'NOTE:' . $escape($originDepartmentName);
        }

        $lines[] = 'X-USERCODE:' . $user->barcode_code;
        $lines[] = 'END:VCARD';

        return implode("\r\n", $lines);
    }

    public function testEmail()
    {
        return view('test-email.index');
    }

    public function updatePhoto(Request $request)
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // only one active profile photo per user — retire whatever was there before
        TrAttachment::where('refnbr', $user->username)
            ->where('doctype', 'AVATAR')
            ->where('status', 'A')
            ->update(['status' => 'X']);

        $meta = [
            'refnbr' => $user->username,
            'doctype' => 'AVATAR',
            // no cpny_id/department_id: a profile photo isn't scoped to one company, and
            // ms_user stores those as comma-separated multi-value lists that don't fit
            // tr_attachment's single-company varchar(10)/varchar(25) columns anyway.
            'base_folder' => 'att-user-profile',
            'created_by' => $user->username,
        ];

        try {
            app(TrAttachmentController::class)->uploadInternal($meta, [$request->file('photo')]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Failed to upload photo', 'error' => $e->getMessage()], 500);
        }

        return response()->json([
            'message' => 'Profile photo updated',
            'url' => $user->fresh()->profile_photo_url,
        ]);
    }
}
