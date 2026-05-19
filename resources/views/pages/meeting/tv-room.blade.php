<!DOCTYPE html>
<html lang="en">

<head>
    <title>{{ $roommeet->room_name ?? $roommeet->name }}</title>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --bg: #020617;
            --card: #0f172acc;
            --border: rgba(255, 255, 255, .08);
            --text: #ffffff;
            --muted: #94a3b8;
            --primary: #38bdf8;
            --success: #22c55e;
            --danger: #ef4444;
        }

        body {
            font-family: 'Inter', sans-serif;
            background:
                radial-gradient(circle at top left, #1e293b 0%, #0f172a 35%, #020617 100%);
            color: var(--text);
            min-height: 100vh;
            overflow: hidden;
        }

        .wrapper {
            min-height: 100vh;
            padding: 2vw;
            display: flex;
            flex-direction: column;
            gap: 1.5vw;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
        }

        .room-section {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .room-section h1 {
            font-size: clamp(32px, 3vw, 58px);
            font-weight: 900;
            letter-spacing: -1px;
            line-height: 1;
        }

        .room-section p {
            font-size: clamp(16px, 1vw, 22px);
            color: var(--muted);
            font-weight: 500;
        }

        .datetime {
            text-align: right;
        }

        .datetime .date {
            font-size: clamp(18px, 1.2vw, 28px);
            font-weight: 700;
            color: #e2e8f0;
        }

        .datetime .clock {
            font-size: clamp(48px, 4vw, 82px);
            font-weight: 900;
            color: var(--primary);
            line-height: 1;
            margin-top: 10px;
        }

        .main {
            flex: 1;
            display: flex;
            gap: 1.5vw;
            min-height: 0;
        }

        .current-card {
            flex: 2;
            position: relative;
            overflow: hidden;
            border-radius: 36px;
            background: var(--card);
            border: 1px solid var(--border);
            backdrop-filter: blur(18px);
            padding: 3vw;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .current-card::before {
            content: '';
            position: absolute;
            width: 700px;
            height: 700px;
            background: rgba(56, 189, 248, .08);
            border-radius: 50%;
            top: -350px;
            right: -220px;
        }

        .current-card::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, .03);
            border-radius: 50%;
            bottom: -220px;
            left: -120px;
        }

        .status {
            position: relative;
            z-index: 2;
            width: max-content;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 24px;
            border-radius: 999px;
            font-size: clamp(16px, 1vw, 20px);
            font-weight: 800;
            margin-bottom: 40px;
            letter-spacing: .5px;
        }

        .status::before {
            content: '';
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: currentColor;
        }

        .available {
            background: rgba(34, 197, 94, .12);
            color: #4ade80;
            border: 1px solid rgba(74, 222, 128, .3);
        }

        .busy {
            background: rgba(239, 68, 68, .12);
            color: #f87171;
            border: 1px solid rgba(248, 113, 113, .3);
        }

        .meeting-time {
            position: relative;
            z-index: 2;
            font-size: clamp(46px, 4vw, 82px);
            font-weight: 900;
            line-height: 1;
            margin-bottom: 28px;
        }

        .meeting-title {
            position: relative;
            z-index: 2;
            font-size: clamp(28px, 2.5vw, 52px);
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 25px;
            max-width: 90%;
        }

        .meeting-user {
            position: relative;
            z-index: 2;
            font-size: clamp(18px, 1.2vw, 26px);
            color: #cbd5e1;
            font-weight: 500;
        }

        .button-group {
            position: relative;
            z-index: 2;
            display: flex;
            gap: 18px;
            margin-top: 50px;
            flex-wrap: wrap;
        }

        .action-btn {
            border: none;
            border-radius: 20px;
            padding: 18px 34px;
            font-size: 18px;
            font-weight: 800;
            cursor: pointer;
            transition: .25s ease;
            color: white;
            min-width: 180px;
        }

        .action-btn:hover {
            transform: translateY(-2px);
        }

        .checkin {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            box-shadow: 0 10px 30px rgba(34, 197, 94, .25);
        }

        .checkout {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            box-shadow: 0 10px 30px rgba(239, 68, 68, .25);
        }

        .sidebar {
            flex: 1;
            min-width: 320px;
            border-radius: 36px;
            background: var(--card);
            border: 1px solid var(--border);
            backdrop-filter: blur(18px);
            padding: 2vw;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px;
        }

        .sidebar-title {
            font-size: clamp(22px, 1.5vw, 34px);
            font-weight: 900;
        }

        .sidebar-badge {
            background: rgba(56, 189, 248, .15);
            color: var(--primary);
            border: 1px solid rgba(56, 189, 248, .25);
            border-radius: 999px;
            padding: 10px 16px;
            font-size: 14px;
            font-weight: 800;
        }

        .schedule-list {
            display: flex;
            flex-direction: column;
            gap: 18px;
            overflow: auto;
            padding-right: 8px;
        }

        .schedule-item {
            background: rgba(255, 255, 255, .04);
            border: 1px solid rgba(255, 255, 255, .06);
            border-radius: 24px;
            padding: 22px;
            transition: .2s ease;
        }

        .schedule-item:hover {
            background: rgba(255, 255, 255, .06);
            transform: translateY(-2px);
        }

        .schedule-time {
            font-size: clamp(18px, 1.2vw, 26px);
            font-weight: 900;
            color: var(--primary);
            margin-bottom: 10px;
        }

        .schedule-title {
            font-size: clamp(18px, 1.1vw, 24px);
            line-height: 1.4;
            font-weight: 700;
        }

        .footer {
            height: 70px;
            border-radius: 22px;
            background: var(--card);
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            overflow: hidden;
            padding: 0 20px;
        }

        marquee {
            font-size: clamp(16px, 1vw, 24px);
            font-weight: 700;
            color: #cbd5e1;
        }

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 999px;
        }

        @media(max-width:1400px) {

            .main {
                flex-direction: column;
            }

            .sidebar {
                min-height: 350px;
            }

            .meeting-title {
                max-width: 100%;
            }
        }

        @media(max-width:900px) {

            body {
                overflow: auto;
            }

            .wrapper {
                padding: 20px;
            }

            .topbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .datetime {
                text-align: left;
            }

            .current-card,
            .sidebar {
                padding: 28px;
            }

            .button-group {
                flex-direction: column;
                width: 100%;
            }

            .action-btn {
                width: 100%;
            }

            .footer {
                height: 55px;
            }
        }

        @media(max-width:600px) {

            .meeting-time {
                line-height: 1.2;
            }

            .current-card,
            .sidebar {
                border-radius: 24px;
            }
        }
    </style>
</head>

<body>

    <div class="wrapper">

        <div class="topbar">

            <div class="room-section">
                <h1>{{ $roommeet->room_name ?? $roommeet->name }}</h1>
                <p>Meeting Room Schedule Display</p>
            </div>

            <div class="datetime">
                <div class="date" id="dateNow"></div>
                <div class="clock" id="clockNow"></div>
            </div>

        </div>

        <div class="main">

            <div class="current-card">

                @if ($meeting == null)

                    <div class="status available">
                        AVAILABLE
                    </div>

                    <div class="meeting-time">
                        Room Available
                    </div>

                    <div class="meeting-title">
                        No ongoing meeting
                    </div>

                    <div class="meeting-user">
                        Ready for next reservation
                    </div>
                @else
                    @php
                        $start_time = \Carbon\Carbon::parse($meeting->start_meeting_time)->format('H:i');
                        $end_time = \Carbon\Carbon::parse($meeting->end_meeting_time)->format('H:i');
                    @endphp

                    <div class="status busy">
                        IN USE
                    </div>

                    <div class="meeting-time">
                        {{ $start_time }} - {{ $end_time }}
                    </div>

                    <div class="meeting-title">
                        {{ $meeting->meeting_title }}
                    </div>

                    <div class="meeting-user">
                        PIC : {{ $meeting->user_peminta }}
                    </div>

                    <div class="button-group">

                        @if ($meeting->checkin == 'N' && $meeting->checkout == 'N')
                            <button class="action-btn checkin"
                                onclick="checkin({{ $meeting->meeting_id ?? $meeting->id }})">
                                Check In
                            </button>
                        @endif

                        @if ($meeting->checkout == 'N' && $meeting->checkin == 'Y')
                            <button class="action-btn checkout"
                                onclick="checkout({{ $meeting->meeting_id ?? $meeting->id }})">
                                Check Out
                            </button>
                        @endif

                    </div>

                @endif

            </div>

            <div class="sidebar">

                <div class="sidebar-header">
                    <div class="sidebar-title">
                        Upcoming Schedule
                    </div>

                    <div class="sidebar-badge">
                        {{ count($meeting2) }} Meetings
                    </div>
                </div>

                <div class="schedule-list">

                    @forelse($meeting2 as $p)
                        @php
                            $start = \Carbon\Carbon::parse($p->start_meeting_time)->format('H:i');
                            $end = \Carbon\Carbon::parse($p->end_meeting_time)->format('H:i');
                        @endphp

                        <div class="schedule-item">

                            <div class="schedule-time">
                                {{ $start }} - {{ $end }}
                            </div>

                            <div class="schedule-title">
                                {{ $p->meeting_title }}
                            </div>

                        </div>

                    @empty

                        <div class="schedule-item">

                            <div class="schedule-title">
                                No upcoming meetings today
                            </div>

                        </div>
                    @endforelse

                </div>

            </div>

        </div>

        <div class="footer">

            <marquee scrollamount="8">
                Welcome to {{ $roommeet->room_name ?? $roommeet->name }} • Meeting Room Schedule Display • Powered by
                IT Department
            </marquee>

        </div>

    </div>

    <script>
        function updateClock() {

            const now = new Date();

            const time = now.toLocaleTimeString('en-GB', {
                hour: '2-digit',
                minute: '2-digit'
            });

            const date = now.toLocaleDateString('en-GB', {
                weekday: 'long',
                day: '2-digit',
                month: 'long',
                year: 'numeric'
            });

            document.getElementById('clockNow').innerHTML = time;
            document.getElementById('dateNow').innerHTML = date;
        }

        updateClock();

        setInterval(updateClock, 1000);

        setInterval(function() {
            location.reload();
        }, 60000);

        function checkin(id) {

            $.ajax({
                url: "{{ url('/checkinroomx') }}_" + id,
                type: "GET",
                data: {
                    checkin: "Y"
                },
                success: function() {
                    location.reload();
                }
            });

        }

        function checkout(id) {

            $.ajax({
                url: "{{ url('/checkoutroomx') }}_" + id,
                type: "GET",
                data: {
                    checkout: "Y"
                },
                success: function() {
                    location.reload();
                }
            });

        }
    </script>

</body>

</html>
