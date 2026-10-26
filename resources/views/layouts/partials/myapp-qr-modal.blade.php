{{-- "Open My APP on your phone" popup. /my-page redirects non-touch devices (laptops) to /dashboard?myapp_qr=1, which lands here.
     Rendered only when that flag is present; the flag is stripped from the address bar so a reload does not pop it up again. --}}
@if (request()->boolean('myapp_qr'))
    @php
        $mpOpenUrl = route('my-page');
        $mpOpenQr = null;
        try {
            $mpOpenQr = 'data:image/png;base64,' . base64_encode(
                (new \BaconQrCode\Writer(new \BaconQrCode\Renderer\GDLibRenderer(360, 2, 'png')))->writeString($mpOpenUrl)
            );
        } catch (\Throwable $e) {
            // no QR (GD missing): the link still works
        }
    @endphp
    <style>
        #mpQrModal {
            position: fixed;
            inset: 0;
            z-index: 2147483000;
            display: grid;
            place-items: center;
            padding: 1.25rem;
            background: #0f172a8c;
            -webkit-backdrop-filter: blur(4px);
            backdrop-filter: blur(4px);
            animation: mpQrFade .2s ease-out;
        }

        #mpQrModal[hidden] {
            display: none;
        }

        #mpQrModal .mpq-card {
            position: relative;
            width: 100%;
            max-width: 24rem;
            padding: 2rem 1.75rem 1.5rem;
            border-radius: 1.75rem;
            text-align: center;
            background: #fff;
            color: #0f172a;
            box-shadow: 0 30px 60px -20px #0b1a6bb3;
            animation: mpQrPop .28s cubic-bezier(.34, 1.4, .64, 1);
        }

        .dark #mpQrModal .mpq-card {
            background: #1e293b;
            color: #f1f5f9;
        }

        #mpQrModal .mpq-x {
            position: absolute;
            top: .75rem;
            right: .75rem;
            width: 2rem;
            height: 2rem;
            display: grid;
            place-items: center;
            border-radius: 9999px;
            color: #64748b;
            background: #f1f5f9;
        }

        .dark #mpQrModal .mpq-x {
            background: #334155;
            color: #cbd5e1;
        }

        #mpQrModal .mpq-ico {
            width: 3.5rem;
            height: 3.5rem;
            margin: 0 auto 1rem;
            display: grid;
            place-items: center;
            border-radius: 1.125rem;
            font-size: 1.5rem;
            color: #fff;
            background: linear-gradient(135deg, #06b6d4, #3b82f6 60%, #6366f1);
            box-shadow: 0 10px 20px -8px #2563eb99;
        }

        #mpQrModal .mpq-t {
            font-size: 1.125rem;
            font-weight: 800;
            line-height: 1.3;
        }

        #mpQrModal .mpq-s {
            margin-top: .5rem;
            font-size: .8125rem;
            line-height: 1.5;
            color: #64748b;
        }

        .dark #mpQrModal .mpq-s {
            color: #94a3b8;
        }

        #mpQrModal .mpq-qr {
            display: block;
            width: 11.25rem;
            height: 11.25rem;
            margin: 1.25rem auto .75rem;
            padding: .5rem;
            border-radius: 1rem;
            background: #fff;
            border: 1px solid #e2e8f0;
            image-rendering: pixelated;
        }

        #mpQrModal .mpq-link {
            display: inline-block;
            max-width: 100%;
            font-size: .75rem;
            font-weight: 600;
            color: #2563eb;
            word-break: break-all;
        }

        @keyframes mpQrFade {
            from { opacity: 0; }
        }

        @keyframes mpQrPop {
            from { opacity: 0; transform: translateY(12px) scale(.96); }
        }

        @media (prefers-reduced-motion: reduce) {
            #mpQrModal, #mpQrModal .mpq-card { animation: none; }
        }
    </style>
    <div id="mpQrModal" role="dialog" aria-modal="true" aria-labelledby="mpQrTitle">
        <div class="mpq-card">
            <button type="button" class="mpq-x" id="mpQrClose" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
            <div class="mpq-ico"><i class="fa-solid fa-mobile-screen-button"></i></div>
            <h2 id="mpQrTitle" class="mpq-t">Open My APP on your phone or tablet</h2>
            <p class="mpq-s">My APP is built for mobile. Scan the QR code with your phone camera, or open the link on your phone
                or tablet. Tip: add it to your home screen to use it like an app.</p>
            @if ($mpOpenQr)
                <img class="mpq-qr" src="{{ $mpOpenQr }}" alt="QR code to open My APP" width="180" height="180">
            @endif
            <a class="mpq-link" href="{{ $mpOpenUrl }}">{{ $mpOpenUrl }}</a>
        </div>
    </div>
    <script>
        (function() {
            var modal = document.getElementById('mpQrModal');
            var close = function() {
                modal.hidden = true;
                document.removeEventListener('keydown', onKey);
            };
            var onKey = function(e) {
                if (e.key === 'Escape') close();
            };
            document.getElementById('mpQrClose').addEventListener('click', close);
            modal.addEventListener('click', function(e) {
                if (e.target === modal) close();
            });
            document.addEventListener('keydown', onKey);
            try {
                var u = new URL(location.href);
                u.searchParams.delete('myapp_qr');
                history.replaceState(null, '', u.pathname + (u.search || '') + u.hash);
            } catch (e) {}
        })();
    </script>
@endif
