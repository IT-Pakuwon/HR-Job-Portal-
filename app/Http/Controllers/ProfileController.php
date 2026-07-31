<?php

namespace App\Http\Controllers;

use App\Models\TrAttachment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $talenta = User::where('username', $user->username)->first();

        return view('profile.show', compact('talenta'));
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
