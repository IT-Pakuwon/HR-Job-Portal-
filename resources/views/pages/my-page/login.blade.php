<!DOCTYPE html>
{{-- My Page sign-in. Lives under /my-page so the installed app can show it without leaving its scope
     (the normal /login is outside it and would open in the phone's browser). --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>My Page</title>
    <link rel="manifest" href="{{ route('my-page.manifest') }}">
    <meta name="theme-color" content="#0b1a6b">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="My Page">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="{{ asset('images/pwa/myapp-180.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/pwa/myapp-192.png') }}">

    <script>
        // Same theme rules as the rest of the app: follow the device when "Follow device setting" is on, else the saved choice.
        (function () {
            try {
                const standalone = window.matchMedia('(display-mode: standalone)').matches || navigator.standalone;
                if (standalone && localStorage.getItem('dark-follow-system') === null) localStorage.setItem('dark-follow-system', 'true');
                const mq = window.matchMedia('(prefers-color-scheme: dark)');
                const follow = localStorage.getItem('dark-follow-system') === 'true';
                const dark = follow ? mq.matches : localStorage.getItem('dark-mode') === 'true';
                const apply = d => { document.documentElement.classList.toggle('dark', d); document.documentElement.style.colorScheme = d ? 'dark' : 'light'; };
                apply(dark);
                mq.addEventListener('change', e => { if (localStorage.getItem('dark-follow-system') === 'true') apply(e.matches); });
            } catch (e) {}
        })();
    </script>

    <style>
        :root { --bg: #eef2f7; --card: #ffffff; --fg: #0f172a; --muted: #64748b; --line: #dbe3ee; --field: #f8fafc; --err: #dc2626; }
        .dark { --bg: #0b1220; --card: #111a2e; --fg: #f1f5f9; --muted: #94a3b8; --line: #243049; --field: #0b1428; --err: #f87171; }
        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        html, body { margin: 0; min-height: 100%; }
        body { font-family: Inter, system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif; background: var(--bg); color: var(--fg); min-height: 100vh; min-height: 100dvh; }
        .wrap { min-height: 100vh; min-height: 100dvh; display: flex; flex-direction: column; }
        .hero { position: relative; overflow: hidden; color: #fff; text-align: center; padding: calc(2.5rem + env(safe-area-inset-top)) 1.5rem 4.5rem;
            background: linear-gradient(130deg, #0e7490 0%, #2563eb 55%, #7c3aed 100%); border-radius: 0 0 2rem 2rem; }
        .hero::before, .hero::after { content: ""; position: absolute; border-radius: 9999px; background: #ffffff1c; }
        .hero::before { width: 14rem; height: 14rem; right: -4rem; top: -6rem; }
        .hero::after { width: 9rem; height: 9rem; left: -3rem; bottom: -4rem; background: #ffffff12; }
        .logo { position: relative; z-index: 1; width: 5rem; height: 5rem; border-radius: 1.375rem; box-shadow: 0 14px 28px -10px #0f172a99; }
        .hero h1 { position: relative; z-index: 1; margin: 1rem 0 .25rem; font-size: 1.5rem; font-weight: 800; }
        .hero p { position: relative; z-index: 1; margin: 0; font-size: .8125rem; opacity: .85; }
        .card { position: relative; z-index: 2; width: calc(100% - 2rem); max-width: 26rem; margin: -2.75rem auto 0; padding: 1.5rem 1.25rem 1.25rem;
            background: var(--card); border: 1px solid var(--line); border-radius: 1.5rem; box-shadow: 0 24px 48px -24px #0f172a66; }
        label { display: block; margin: 0 0 .375rem; font-size: .75rem; font-weight: 600; color: var(--muted); }
        .field { margin-bottom: 1rem; }
        .field input[type="text"], .field input[type="password"] { width: 100%; padding: .8125rem 1rem; font-size: 1rem; color: var(--fg); background: var(--field);
            border: 1px solid var(--line); border-radius: .875rem; outline: none; transition: border-color .15s, box-shadow .15s; }
        .field input:focus { border-color: #2563eb; box-shadow: 0 0 0 3px #2563eb30; }
        .pw { position: relative; }
        .pw button { position: absolute; right: .5rem; top: 50%; transform: translateY(-50%); border: 0; background: transparent; color: var(--muted); padding: .5rem; font-size: .75rem; font-weight: 600; cursor: pointer; }
        .row { display: flex; align-items: center; justify-content: space-between; gap: .75rem; margin: .25rem 0 1.125rem; font-size: .8125rem; }
        .row label { margin: 0; display: flex; align-items: center; gap: .5rem; font-weight: 500; color: var(--fg); }
        .row a { color: #2563eb; font-weight: 600; text-decoration: none; }
        .dark .row a { color: #60a5fa; }
        .btn { width: 100%; padding: .875rem 1rem; font-size: .9375rem; font-weight: 700; color: #fff; border: 0; border-radius: .875rem; cursor: pointer;
            background: linear-gradient(120deg, #0e7490, #2563eb 60%, #7c3aed); box-shadow: 0 14px 24px -12px #2563eb; transition: transform .15s; }
        .btn:active { transform: scale(.98); }
        .err { margin: 0 0 1rem; padding: .625rem .75rem; border-radius: .75rem; font-size: .8125rem; color: var(--err); background: color-mix(in srgb, var(--err) 12%, transparent); border: 1px solid color-mix(in srgb, var(--err) 35%, transparent); }
        .foot { margin: auto 0 0; padding: 1.5rem 1rem calc(1.25rem + env(safe-area-inset-bottom)); text-align: center; font-size: .6875rem; color: var(--muted); }
    </style>
</head>

<body>
    <div class="wrap">
        <div class="hero">
            <img class="logo" src="{{ asset('images/pwa/myapp-192.png') }}" alt="My APP">
            <h1>My Page</h1>
            <p>Sign in to see your approvals, documents and messages</p>
        </div>

        <form class="card" method="POST" action="{{ route('my-page.login.submit') }}">
            @csrf

            @if ($errors->any())
                <div class="err">{{ $errors->first() }}</div>
            @elseif (session('error'))
                <div class="err">{{ session('error') }}</div>
            @endif

            <div class="field">
                <label for="login">Email, username or NPK</label>
                <input id="login" name="login" type="text" value="{{ old('login') }}" autocomplete="username" autocapitalize="none" autocorrect="off" required autofocus>
            </div>

            <div class="field">
                <label for="password">Password</label>
                <div class="pw">
                    <input id="password" name="password" type="password" autocomplete="current-password" required style="padding-right: 4.5rem;">
                    <button type="button" id="togglePw" aria-label="Show password">Show</button>
                </div>
            </div>

            <div class="row">
                <label><input type="checkbox" name="remember" value="1" checked> Keep me signed in</label>
                {{-- outside this app's scope, so it opens in the browser --}}
                <a href="{{ route('password.request') }}" target="_blank" rel="noopener noreferrer">Forgot password?</a>
            </div>

            <button type="submit" class="btn">Sign in</button>
        </form>

        <div class="foot">Pakuwon System</div>
    </div>

    <script>
        document.getElementById('togglePw').addEventListener('click', function () {
            const i = document.getElementById('password');
            const show = i.type === 'password';
            i.type = show ? 'text' : 'password';
            this.textContent = show ? 'Hide' : 'Show';
        });
    </script>
</body>

</html>
