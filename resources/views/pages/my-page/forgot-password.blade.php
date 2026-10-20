@extends('pages.my-page.auth-layout')

@section('title', 'Forgot password · My APP')
@section('heading', 'Forgot your password?')
@section('tagline', "No worries — we'll get you back in.")

@section('card')
    <form class="card" method="POST" action="{{ route('my-page.forgot.submit') }}">
        @csrf

        @if ($errors->any())
            <div class="err">{{ $errors->first() }}</div>
        @elseif (session('status'))
            <div class="ok">{{ session('status') }}</div>
        @endif

        <p class="lead">Enter your email or username and we'll email you a link to choose a new password.</p>

        <div class="field">
            <label for="login">Email or username</label>
            <input id="login" name="login" type="text" value="{{ old('login') }}" autocomplete="username" autocapitalize="none" autocorrect="off" placeholder="you@company.com" required autofocus>
        </div>

        <button type="submit" class="btn">Send reset link</button>
        <a class="back" href="{{ route('my-page.login') }}">&larr; Back to sign in</a>
    </form>
@endsection
