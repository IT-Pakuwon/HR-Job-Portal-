<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class TestEmailController extends Controller
{
    public function index()
    {
        return view('pages.test-email.index', [
            'mailConfig' => [
                'mailer'       => config('mail.default'),
                'host'         => config('mail.mailers.smtp.host'),
                'port'         => config('mail.mailers.smtp.port'),
                'username'     => config('mail.mailers.smtp.username'),
                'password'     => config('mail.mailers.smtp.password'),
                'encryption'   => config('mail.mailers.smtp.encryption'),
                'from_address' => config('mail.from.address'),
                'from_name'    => config('mail.from.name'),
            ]
        ]);
    }

    public function send(Request $request)
    {
        $request->validate([
            'from'    => ['required', 'email'],
            'to'      => ['required', 'email'],
            'subject' => ['required', 'string', 'max:255'],
            'body'    => ['required', 'string'],
        ]);

        try {
            $from = $request->input('from');
            $to = $request->input('to');
            $subject = $request->input('subject');
            $body = $request->input('body');
            $fromName = config('mail.from.name', config('app.name'));

            Mail::html(
                nl2br(e($body)),
                function ($message) use ($from, $fromName, $to, $subject) {
                    $message->from($from, $fromName);
                    $message->to($to);
                    $message->subject($subject);
                }
            );

            return back()->with('success', 'Email berhasil dikirim.');
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal kirim email: ' . $e->getMessage());
        }
    }
}