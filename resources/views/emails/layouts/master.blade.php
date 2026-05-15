<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        @yield('title')
    </title>

</head>

<body style="
    margin:0;
    padding:32px 16px;
    background:#f3f4f6;
    font-family:
        Inter,
        Arial,
        sans-serif;
    color:#111827;
">

    <div style="
        max-width:720px;
        margin:0 auto;
    ">

        <div style="
            overflow:hidden;
            border-radius:24px;
            background:#ffffff;
            border:1px solid #e5e7eb;
            box-shadow:
                0 10px 30px rgba(0,0,0,0.06);
        ">

            <div style="
                padding:32px 36px;
                background:
                    linear-gradient(
                        135deg,
                        #111827 0%,
                        #1f2937 100%
                    );
            ">

                <div style="
                    display:inline-flex;
                    align-items:center;
                    justify-content:center;
                    width:52px;
                    height:52px;
                    border-radius:16px;
                    background:
                        rgba(255,255,255,0.12);
                    font-size:24px;
                    margin-bottom:20px;
                ">
                    @yield('icon')
                </div>

                <h1 style="
                    margin:0;
                    font-size:28px;
                    font-weight:700;
                    color:#ffffff;
                    letter-spacing:-0.03em;
                ">
                    @yield('header')
                </h1>

                <p style="
                    margin:12px 0 0;
                    font-size:14px;
                    line-height:24px;
                    color:#d1d5db;
                ">
                    @yield('subtitle')
                </p>

            </div>

            <div style="
                padding:36px;
            ">

                @yield('content')

            </div>

            <div style="
                padding:24px 36px;
                background:#f9fafb;
                border-top:1px solid #e5e7eb;
            ">

                <div style="
                    font-size:12px;
                    line-height:22px;
                    color:#6b7280;
                ">

                    This is an automated email from
                    IT Ticketing System.

                    <br><br>

                    Please do not reply directly to this
                    email.

                </div>

            </div>

        </div>

    </div>

</body>

</html>
