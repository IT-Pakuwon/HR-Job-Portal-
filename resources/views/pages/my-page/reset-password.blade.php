@extends('pages.my-page.auth-layout')

@section('title', 'Reset password · My APP')
@section('heading', 'Choose a new password')
@section('tagline', 'Make it a good one.')

@section('card')
    @if (!$valid)
        <div class="card">
            <div class="err">This reset link has already been used or has expired.</div>
            <a class="btn" style="display:block;text-align:center;text-decoration:none;" href="{{ route('my-page.forgot') }}">Request a new link</a>
        </div>
    @else
        <form class="card" method="POST" action="{{ route('my-page.reset.submit') }}">
            @csrf
            <input type="hidden" name="u" value="{{ $u }}">
            <input type="hidden" name="s" value="{{ $s }}">

            @if ($errors->any())
                <div class="err">{{ $errors->first() }}</div>
            @endif

            <p class="lead">Resetting the password for <strong>{{ $u }}</strong>.</p>

            <div class="field">
                <label for="password">New password</label>
                <input id="password" name="password" type="password" autocomplete="new-password" required autofocus>
                <p class="hint">At least 8 characters.</p>
            </div>

            <div class="field">
                <label for="password_confirmation">Confirm new password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
            </div>

            <button type="submit" class="btn">Update password</button>
        </form>
    @endif
@endsection
