@extends('pages.my-page.auth-layout')

@section('title', 'My APP')
@section('heading', 'My APP')
@section('tagline', 'Your approvals, documents & messages — right in your pocket.')

@section('card')
    <form class="card" method="POST" action="{{ route('my-page.login.submit') }}">
        @csrf

        @if ($errors->any())
            <div class="err">{{ $errors->first() }}</div>
        @elseif (session('error'))
            <div class="err">{{ session('error') }}</div>
        @elseif (session('status'))
            <div class="ok">{{ session('status') }}</div>
        @endif

        <div class="field">
            <label for="login">Email or username</label>
            <input id="login" name="login" type="text" value="{{ old('login') }}" autocomplete="username" autocapitalize="none" autocorrect="off" placeholder="you@company.com" required autofocus>
        </div>

        <div class="field">
            <label for="password">Password</label>
            <div class="pw">
                <input id="password" name="password" type="password" autocomplete="current-password" placeholder="Your password" required style="padding-right: 4.5rem;">
                <button type="button" id="togglePw" aria-label="Show password">Show</button>
            </div>
        </div>

        <div class="row">
            <label><input type="checkbox" name="remember" value="1" checked> Keep me signed in</label>
            <a href="{{ route('my-page.forgot') }}">Forgot password?</a>
        </div>

        <button type="submit" class="btn">Let's go</button>
    </form>
@endsection

@push('scripts')
    <script>
        document.getElementById('togglePw').addEventListener('click', function () {
            const i = document.getElementById('password');
            const show = i.type === 'password';
            i.type = show ? 'text' : 'password';
            this.textContent = show ? 'Hide' : 'Show';
        });
    </script>
@endpush
