<!DOCTYPE html>
<html lang="en">

<head>
    <title>{{ $roommeet->room_name ?? $roommeet->name }}</title>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        :root{
            --bg:#020617;
            --card:#081028cc;
            --border:rgba(255,255,255,.08);
            --text:#ffffff;
            --muted:#94a3b8;
            --primary:#38bdf8;
            --success:#22c55e;
            --danger:#ef4444;
        }

        body{
            font-family:'Inter',sans-serif;
            background:
                radial-gradient(circle at top left,#1e293b 0%,#0f172a 35%,#020617 100%);
            color:var(--text);
            min-height:100vh;
            overflow:hidden;
        }

        .wrapper{
            min-height:100vh;
            padding:2vw;
            display:flex;
            flex-direction:column;
            gap:1.5vw;
        }

        .topbar{
            display:flex;
            justify-content:space-between;
            align-items:flex-start;
            gap:20px;
        }

        .room-section{
            display:flex;
            flex-direction:column;
            gap:10px;
        }

        .room-section h1{
            font-size:clamp(34px,3vw,64px);
            font-weight:900;
            letter-spacing:-1px;
            line-height:1;
        }

        .room-section p{
            font-size:clamp(16px,1vw,22px);
            color:var(--muted);
            font-weight:500;
        }

        .datetime{
            text-align:right;
        }

        .datetime .date{
            font-size:clamp(18px,1.2vw,28px);
            font-weight:700;
            color:#e2e8f0;
        }

        .datetime .clock{
            font-size:clamp(54px,4vw,90px);
            font-weight:900;
            color:var(--primary);
            line-height:1;
            margin-top:10px;
        }

        .main{
            flex:1;
            display:flex;
            min-height:0;
        }

        .current-card{
            width:100%;
            position:relative;
            overflow:hidden;
            border-radius:40px;
            background:var(--card);
            border:1px solid var(--border);
            backdrop-filter:blur(18px);
            padding:4vw;
            display:flex;
            flex-direction:column;
            justify-content:center;
        }

        .current-card::before{
            content:'';
            position:absolute;
            width:900px;
            height:900px;
            background:rgba(56,189,248,.08);
            border-radius:50%;
            top:-450px;
            right:-250px;
        }

        .current-card::after{
            content:'';
            position:absolute;
            width:500px;
            height:500px;
            background:rgba(255,255,255,.03);
            border-radius:50%;
            bottom:-280px;
            left:-150px;
        }

        .status{
            position:relative;
            z-index:2;
            width:max-content;
            display:flex;
            align-items:center;
            gap:12px;
            padding:14px 24px;
            border-radius:999px;
            font-size:clamp(16px,1vw,20px);
            font-weight:800;
            margin-bottom:40px;
            letter-spacing:.5px;
        }

        .status::before{
            content:'';
            width:12px;
            height:12px;
            border-radius:50%;
            background:currentColor;
        }

        .available{
            background:rgba(34,197,94,.12);
            color:#4ade80;
            border:1px solid rgba(74,222,128,.3);
        }

        .busy{
            background:rgba(239,68,68,.12);
            color:#f87171;
            border:1px solid rgba(248,113,113,.3);
        }

        .meeting-time{
            position:relative;
            z-index:2;
            font-size:clamp(58px,5vw,110px);
            font-weight:900;
            line-height:1;
            margin-bottom:35px;
        }

        .meeting-title{
            position:relative;
            z-index:2;
            font-size:clamp(36px,3vw,72px);
            font-weight:900;
            line-height:1.15;
            margin-bottom:28px;
            max-width:80%;
        }

        .meeting-user{
            position:relative;
            z-index:2;
            font-size:clamp(20px,1.3vw,30px);
            color:#cbd5e1;
            font-weight:500;
        }

        .button-group{
            position:relative;
            z-index:2;
            display:flex;
            gap:18px;
            margin-top:60px;
            flex-wrap:wrap;
        }

        .action-btn{
            border:none;
            border-radius:22px;
            padding:20px 40px;
            font-size:20px;
            font-weight:800;
            cursor:pointer;
            transition:.25s ease;
            color:white;
            min-width:200px;
        }

        .action-btn:hover{
            transform:translateY(-2px);
        }

        .checkin{
            background:linear-gradient(135deg,#22c55e,#16a34a);
            box-shadow:0 10px 30px rgba(34,197,94,.25);
        }

        .checkout{
            background:linear-gradient(135deg,#ef4444,#dc2626);
            box-shadow:0 10px 30px rgba(239,68,68,.25);
        }

        .next-meeting{
            position:absolute;
            right:45px;
            bottom:45px;
            z-index:2;
            min-width:420px;
            max-width:500px;
            background:rgba(255,255,255,.05);
            border:1px solid rgba(255,255,255,.08);
            border-radius:28px;
            padding:24px;
            backdrop-filter:blur(12px);
        }

        .next-label{
            font-size:14px;
            font-weight:800;
            color:#38bdf8;
            letter-spacing:1px;
            margin-bottom:12px;
        }

        .next-time{
            font-size:32px;
            font-weight:900;
            margin-bottom:10px;
        }

        .next-title{
            font-size:22px;
            font-weight:700;
            line-height:1.4;
            color:#e2e8f0;
        }

        .footer{
            height:70px;
            border-radius:22px;
            background:var(--card);
            border:1px solid var(--border);
            display:flex;
            align-items:center;
            overflow:hidden;
            padding:0 20px;
        }

        marquee{
            font-size:clamp(16px,1vw,24px);
            font-weight:700;
            color:#cbd5e1;
        }

        @media(max-width:1200px){

            .meeting-title{
                max-width:100%;
            }

            .current-card{
                padding:40px;
            }

            .next-meeting{
                position:relative;
                right:auto;
                bottom:auto;
                margin-top:50px;
                max-width:100%;
            }
        }

        @media(max-width:768px){

            body{
                overflow:auto;
            }

            .wrapper{
                padding:20px;
            }

            .topbar{
                flex-direction:column;
                align-items:flex-start;
            }

            .datetime{
                text-align:left;
            }

            .button-group{
                flex-direction:column;
                width:100%;
            }

            .action-btn{
                width:100%;
            }

            .footer{
                height:55px;
            }

            .current-card{
                border-radius:28px;
            }

            .next-meeting{
                min-width:100%;
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

            @if(count($meeting2) > 0)

                @php
                    $next = $meeting2->first();

                    $next_start = \Carbon\Carbon::parse($next->start_meeting_time)->format('H:i');
                    $next_end = \Carbon\Carbon::parse($next->end_meeting_time)->format('H:i');
                @endphp

                <div class="next-meeting">

                    <div class="next-label">
                        NEXT MEETING
                    </div>

                    <div class="next-time">
                        {{ $next_start }} - {{ $next_end }}
                    </div>

                    <div class="next-title">
                        {{ $next->meeting_title }}
                    </div>

                </div>

            @endif

        </div>

    </div>

    <div class="footer">

        <marquee scrollamount="8">

            @if(count($meeting2) > 0)

                @foreach($meeting2 as $item)

                    @php
                        $footer_start = \Carbon\Carbon::parse($item->start_meeting_time)->format('H:i');
                        $footer_end = \Carbon\Carbon::parse($item->end_meeting_time)->format('H:i');
                    @endphp

                    NEXT : {{ $footer_start }} - {{ $footer_end }} • {{ $item->meeting_title }} •••

                @endforeach

            @else

                Welcome to {{ $roommeet->room_name ?? $roommeet->name }} • No Upcoming Meetings Today

            @endif

        </marquee>

    </div>

</div>

<script>

    function updateClock(){

        const now = new Date();

        const time = now.toLocaleTimeString('en-GB',{
            hour:'2-digit',
            minute:'2-digit'
        });

        const date = now.toLocaleDateString('en-GB',{
            weekday:'long',
            day:'2-digit',
            month:'long',
            year:'numeric'
        });

        document.getElementById('clockNow').innerHTML = time;
        document.getElementById('dateNow').innerHTML = date;
    }

    updateClock();

    setInterval(updateClock,1000);

    setInterval(function(){
        location.reload();
    },60000);

    function checkin(id){

        $.ajax({
            url:"{{ url('/checkinroomx') }}_"+id,
            type:"GET",
            data:{
                checkin:"Y"
            },
            success:function(){
                location.reload();
            }
        });

    }

    function checkout(id){

        $.ajax({
            url:"{{ url('/checkoutroomx') }}_"+id,
            type:"GET",
            data:{
                checkout:"Y"
            },
            success:function(){
                location.reload();
            }
        });

    }

</script>

</body>
</html>
