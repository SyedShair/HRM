@php

$days = [
    'Monday',
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday',
    'Saturday',
    'Sunday',
];

@endphp

@extends('layouts.default')

@section('meta')
    <title>Today Shifts | Jpingos</title>
@endsection

@section('content')
<style>

:root{

    --primary:#06b6d4;
    --primary-dark:#0891b2;
    --secondary:#8b5cf6;
    --success:#22c55e;
    --danger:#ef4444;
    --warning:#f59e0b;

    --bg:#f0f9ff;
    --white:#ffffff;
    --dark:#0f172a;
    --gray:#64748b;
    --border:#dbeafe;

    --morning:#fbbf24;
    --day:#38bdf8;
    --evening:#a855f7;
    --night:#334155;

}

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{

    font-family:'Poppins',sans-serif;
    background:
        radial-gradient(circle at top left,#cffafe,#f0f9ff),
        radial-gradient(circle at bottom right,#ddd6fe,#f8fafc);

    min-height:100vh;
    color:var(--dark);
    overflow-x:hidden;
}

/* ANIMATED BACKGROUND */

body::before{

    content:'';
    position:fixed;
    width:500px;
    height:500px;
    background:rgba(14,165,233,0.15);
    border-radius:50%;
    top:-150px;
    right:-150px;
    animation:float1 10s infinite alternate;
    z-index:-1;
}

body::after{

    content:'';
    position:fixed;
    width:400px;
    height:400px;
    background:rgba(168,85,247,0.12);
    border-radius:50%;
    bottom:-150px;
    left:-100px;
    animation:float2 12s infinite alternate;
    z-index:-1;
}

@keyframes float1{

    from{
        transform:translateY(0px);
    }

    to{
        transform:translateY(50px);
    }

}

@keyframes float2{

    from{
        transform:translateX(0px);
    }

    to{
        transform:translateX(40px);
    }

}

main{

    max-width:1500px;
    margin:auto;
    padding:30px;
}

/* HEADER */

.rota-head{

    background:linear-gradient(135deg,#06b6d4,#8b5cf6);
    color:white;

    padding:30px;
    border-radius:24px;

    display:flex;
    justify-content:space-between;
    align-items:center;
    flex-wrap:wrap;

    margin-bottom:25px;

    box-shadow:
        0 10px 40px rgba(6,182,212,0.3);

    animation:fadeDown 0.8s ease;
}

@keyframes fadeDown{

    from{
        opacity:0;
        transform:translateY(-30px);
    }

    to{
        opacity:1;
        transform:translateY(0);
    }

}

.rota-head h2{

    font-size:38px;
    font-weight:800;
    margin-bottom:8px;
}

.rota-head p{

    opacity:0.9;
    font-size:15px;
}

.live-badge{

    background:white;
    color:#06b6d4;
    padding:12px 20px;
    border-radius:50px;
    font-weight:700;
    font-size:14px;

    animation:pulse 2s infinite;
}

@keyframes pulse{

    0%{
        transform:scale(1);
    }

    50%{
        transform:scale(1.05);
    }

    100%{
        transform:scale(1);
    }

}

/* LEGEND */

.legend{

    display:flex;
    gap:14px;
    flex-wrap:wrap;
    margin-bottom:25px;
}

.legend-item{

    background:white;
    padding:10px 16px;
    border-radius:50px;

    display:flex;
    align-items:center;
    gap:8px;

    font-size:13px;
    font-weight:600;

    box-shadow:0 4px 12px rgba(0,0,0,0.06);

    transition:0.3s;
}

.legend-item:hover{

    transform:translateY(-3px);
}

.legend-color{

    width:16px;
    height:16px;
    border-radius:5px;
}

.off{
    background:#e5e7eb;
}

.morning{
    background:var(--morning);
}

.day{
    background:var(--day);
}

.evening{
    background:var(--evening);
}

.night{
    background:var(--night);
}

/* PANEL */

.rota-panel{

    background:rgba(255,255,255,0.75);
    backdrop-filter:blur(15px);

    border-radius:24px;
    overflow:auto;

    border:1px solid rgba(255,255,255,0.4);

    box-shadow:
        0 15px 50px rgba(15,23,42,0.08);

    animation:fadeUp 1s ease;
}

@keyframes fadeUp{

    from{
        opacity:0;
        transform:translateY(30px);
    }

    to{
        opacity:1;
        transform:translateY(0);
    }

}

.rota-grid{

    display:grid;
    grid-template-columns:280px repeat(7,1fr) 130px;
    min-width:1300px;
}

.grid-cell{

    padding:18px;
    border-right:1px solid rgba(226,232,240,0.6);
    border-bottom:1px solid rgba(226,232,240,0.6);
}

/* HEADER */

.grid-header{

    background:linear-gradient(135deg,#f8fafc,#dbeafe);

    text-align:center;
    font-weight:700;

    color:var(--dark);

    position:sticky;
    top:0;
    z-index:10;
}

.grid-header:hover{

    background:linear-gradient(135deg,#e0f2fe,#dbeafe);
}

/* STAFF */

.staff-cell{

    display:flex;
    align-items:center;
    gap:14px;
}

.staff-avatar{

    width:55px;
    height:55px;

    border-radius:18px;

    background:linear-gradient(135deg,#06b6d4,#8b5cf6);

    color:white;

    display:flex;
    align-items:center;
    justify-content:center;

    font-weight:800;
    font-size:18px;

    box-shadow:
        0 8px 18px rgba(6,182,212,0.3);

    transition:0.3s;
}

.staff-avatar:hover{

    transform:rotate(6deg) scale(1.08);
}

.staff-name{

    font-weight:700;
    font-size:15px;
}

.staff-role{

    color:var(--gray);
    font-size:12px;
    margin-top:4px;
}

/* SHIFTS */

.shift-cell{

    border-radius:18px;

    padding:14px 10px;

    text-align:center;

    font-size:13px;
    font-weight:700;
    color:white;

    transition:0.3s;

    cursor:pointer;

    position:relative;
    overflow:hidden;
}

.shift-cell::before{

    content:'';
    position:absolute;
    width:120%;
    height:120%;
    background:rgba(255,255,255,0.18);

    top:-120%;
    left:-120%;

    transform:rotate(45deg);

    transition:0.5s;
}

.shift-cell:hover::before{

    top:100%;
    left:100%;
}

.shift-cell:hover{

    transform:translateY(-5px) scale(1.03);

    box-shadow:
        0 12px 25px rgba(0,0,0,0.12);
}

.shift-cell.off{

    background:#f1f5f9;
    color:#94a3b8;
}

.shift-cell.morning{

    background:linear-gradient(135deg,#fbbf24,#f59e0b);
}

.shift-cell.day{

    background:linear-gradient(135deg,#38bdf8,#0ea5e9);
}

.shift-cell.evening{

    background:linear-gradient(135deg,#a855f7,#7c3aed);
}

.shift-cell.night{

    background:linear-gradient(135deg,#334155,#0f172a);
}

/* TOTAL */

.total-box{

    background:linear-gradient(135deg,#cffafe,#e0f2fe);

    color:#0369a1;

    text-align:center;
    font-weight:800;
    font-size:16px;

    display:flex;
    align-items:center;
    justify-content:center;

    transition:0.3s;
}

.total-box:hover{

    background:linear-gradient(135deg,#06b6d4,#0891b2);
    color:white;
}

/* MOBILE */

@media(max-width:768px){

    main{
        padding:15px;
    }

    .rota-head{
        padding:22px;
    }

    .rota-head h2{
        font-size:28px;
    }

}
.download-btn{
    display:inline-block;
    background:#06b6d4;
    color:white;
    padding:12px 20px;
    border-radius:10px;
    text-decoration:none;
    font-weight:700;
    margin-bottom:20px;
}

</style>

</head>

<body>

<main>

<div class="rota-head">

    <div>

        <h2>✨ Weekly Staff Rota</h2>

        <p>
            Weekly Shift Management Dashboard
        </p>

    </div>

   

       <a href="{{ route('rota.pdf') }}" target="_blank" class="download-btn live-badge">
    Download PDF
</a>

    

</div>


<div class="legend">

    <div class="legend-item">
        <div class="legend-color off"></div>
        OFF DAY
    </div>

    <div class="legend-item">
        <div class="legend-color morning"></div>
        MORNING SHIFT
    </div>

    <div class="legend-item">
        <div class="legend-color day"></div>
        DAY SHIFT
    </div>

    <div class="legend-item">
        <div class="legend-color evening"></div>
        EVENING SHIFT
    </div>

    <div class="legend-item">
        <div class="legend-color night"></div>
        NIGHT SHIFT
    </div>

</div>


<div class="rota-panel">

<div class="rota-grid">

    {{-- HEADER --}}

    <div class="grid-cell grid-header">
        👨‍💼 STAFF
    </div>

    @foreach($days as $day)

        <div class="grid-cell grid-header">
            {{ $day }}
        </div>

    @endforeach

    <div class="grid-cell grid-header">
        ⏱ HOURS
    </div>


    {{-- EMPLOYEES --}}

    @foreach($employees as $employee)

        @php

            $totalHours = 0;

            $fullname =
                strtoupper($employee->lastname . ', ' . $employee->firstname);

            $initials =
                strtoupper(substr($employee->firstname,0,1)) .
                strtoupper(substr($employee->lastname,0,1));

        @endphp


        <div class="grid-cell">

            <div class="staff-cell">

                <div class="staff-avatar">

                    {{ $initials }}

                </div>

                <div>

                    <div class="staff-name">

                        {{ $fullname }}

                    </div>

                    <div class="staff-role">

                        Employee ID: {{ $employee->idno }}

                    </div>

                </div>

            </div>

        </div>


        {{-- DAYS --}}

        @foreach($days as $day)

            @php

                $shift = $weeklyShifts
                    ->where('reference', $employee->reference)
                    ->where('day', $day)
                    ->first();

                $class = 'off';
                $label = 'OFF';

                if($shift){

                    if($shift->is_off == 0){

                        $timeIn = date('H:i', strtotime($shift->time_in));
                        $timeOut = date('H:i', strtotime($shift->time_out));

                        $hour = date('H', strtotime($shift->time_in));

                        if($hour < 12){
                            $class = 'morning';
                        }
                        elseif($hour < 16){
                            $class = 'day';
                        }
                        elseif($hour < 20){
                            $class = 'evening';
                        }
                        else{
                            $class = 'night';
                        }

                        $label = $timeIn . ' → ' . $timeOut;

                        $totalHours +=
                            (strtotime($shift->time_out) - strtotime($shift->time_in)) / 3600;
                    }

                }

            @endphp

            <div class="grid-cell">

                <div class="shift-cell {{ $class }}">

                    {{ $label }}

                </div>

            </div>

        @endforeach


        {{-- TOTAL HOURS --}}

        <div class="grid-cell total-box">

            {{ number_format($totalHours,1) }} hrs

        </div>

    @endforeach

</div>

</div>

</main>

@endsection
