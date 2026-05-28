<?php

namespace App\Http\Controllers;

use App\Models\User;
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
}
