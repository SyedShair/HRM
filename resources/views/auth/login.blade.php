<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">

<title>Employee Login</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>

:root{
    --brand-900:#13201C;
    --brand-800:#2B3D37;
    --brand-700:#3E5B54;
    --brand-600:#3E5B54;
    --accent:#607570;
    --accent-dark:#3E5B54;
    --ink:#222222;
    --muted:#5F6C76;
    --border:#eeeeee;
    --bg:#3E5B54;
    --danger:#dc2626;
    --success:#16a34a;
}

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

html,body{
    height:100%;
}

body{
    font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;
    background:var(--bg);
    color:var(--ink);
    min-height:100vh;
    min-height:100dvh;
}

/* =========================
   LAYOUT
========================= */

.auth-shell{
    display:grid;
    grid-template-columns:minmax(0,1fr) minmax(0,1fr);
    min-height:100vh;
    min-height:100dvh;
}

/* =========================
   BRAND PANEL
========================= */

.brand-panel{
    position:relative;
    display:flex;
    flex-direction:column;
    justify-content:space-between;
    padding:clamp(32px, 5vw, 64px);
    background:linear-gradient(155deg, var(--brand-900) 0%, var(--brand-800) 45%, var(--brand-600) 100%);
    color:#fff;
    overflow:hidden;
}

.brand-panel::before{
    content:"";
    position:absolute;
    inset:0;
    background-image:
        radial-gradient(circle at 15% 20%, rgba(255,255,255,0.06) 0, transparent 40%),
        radial-gradient(circle at 85% 75%, rgba(255,255,255,0.06) 0, transparent 45%);
    pointer-events:none;
}

.brand-panel::after{
    content:"";
    position:absolute;
    right:-20%;
    top:-10%;
    width:70%;
    height:70%;
    background:
        linear-gradient(transparent 0 calc(100% - 1px), rgba(255,255,255,.06) 0) 0 0/48px 48px,
        linear-gradient(90deg, transparent 0 calc(100% - 1px), rgba(255,255,255,.06) 0) 0 0/48px 48px;
    -webkit-mask-image:radial-gradient(circle at center, #000 0%, transparent 70%);
    mask-image:radial-gradient(circle at center, #000 0%, transparent 70%);
    pointer-events:none;
}

.brand-top{
    position:relative;
    z-index:1;
    display:flex;
    align-items:center;
    gap:12px;
}

.brand-mark{
    width:40px;
    height:40px;
    border-radius:10px;
    background:rgba(255,255,255,0.12);
    border:1px solid rgba(255,255,255,0.18);
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:700;
    font-size:16px;
    flex-shrink:0;
}

.brand-name{
    font-weight:700;
    font-size:17px;
    letter-spacing:.2px;
}

.brand-middle{
    position:relative;
    z-index:1;
    max-width:460px;
}

.brand-middle h1{
    font-size:clamp(26px, 3.4vw, 38px);
    font-weight:800;
    line-height:1.2;
    margin-bottom:16px;
    letter-spacing:-.5px;
}

.brand-middle p{
    font-size:15px;
    line-height:1.65;
    color:rgba(255,255,255,0.72);
    margin-bottom:28px;
}

.brand-points{
    list-style:none;
    display:flex;
    flex-direction:column;
    gap:14px;
}

.brand-points li{
    display:flex;
    align-items:flex-start;
    gap:12px;
    font-size:14px;
    color:rgba(255,255,255,0.85);
}

.brand-points i{
    width:22px;
    height:22px;
    border-radius:6px;
    background:rgba(255,255,255,0.14);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:12px;
    flex-shrink:0;
    margin-top:1px;
}

.brand-bottom{
    position:relative;
    z-index:1;
    font-size:12.5px;
    color:rgba(255,255,255,0.5);
}

/* =========================
   FORM PANEL
========================= */

.form-panel{
    display:flex;
    align-items:center;
    justify-content:center;
    padding:clamp(20px, 5vw, 48px);
    background:#fff;
}

.form-shell{
    width:100%;
    max-width:400px;
}

.form-shell-head{
    margin-bottom:30px;
}

.form-shell-head h2{
    font-size:24px;
    font-weight:700;
    color:var(--ink);
    margin-bottom:6px;
    letter-spacing:-.3px;
}

.form-shell-head p{
    font-size:14px;
    color:var(--muted);
}

/* =========================
   ALERTS
========================= */

.alert{
    border-radius:10px;
    font-size:13.5px;
    padding:12px 14px;
    border:1px solid transparent;
    margin-bottom:20px;
}

.alert-success{
    background:#f0fdf4;
    border-color:#bbf7d0;
    color:#15803d;
}

.alert-danger{
    background:#fef2f2;
    border-color:#fecaca;
    color:#b91c1c;
}

/* =========================
   TABS
========================= */

.login-tabs{
    display:flex;
    background:#f1f5f9;
    padding:4px;
    border-radius:10px;
    margin-bottom:26px;
    position:relative;
}

.login-tabs button{
    flex:1;
    position:relative;
    z-index:1;
    border:none;
    background:none;
    color:var(--muted);
    padding:10px;
    border-radius:8px;
    font-weight:600;
    font-size:13.5px;
    transition:color .25s ease;
    cursor:pointer;
}

.login-tabs button.active{
    background:#fff;
    color:var(--ink);
    box-shadow:0 1px 3px rgba(15,23,42,0.12), 0 1px 2px rgba(15,23,42,0.08);
}

/* =========================
   FORM
========================= */

.form-group{
    margin-bottom:18px;
}

.form-label{
    display:block;
    color:var(--ink);
    font-size:13px;
    font-weight:600;
    margin-bottom:7px;
}

.input-wrap{
    position:relative;
}

.input-wrap i{
    position:absolute;
    left:14px;
    top:50%;
    transform:translateY(-50%);
    color:#94a3b8;
    font-size:15px;
    pointer-events:none;
    transition:color .2s ease;
}

.form-control{
    height:46px;
    width:100%;
    border:1px solid var(--border);
    border-radius:10px;
    background:#f8fafc;
    color:var(--ink);
    padding:0 14px 0 40px;
    font-size:14px;
    transition:border-color .2s ease, background .2s ease, box-shadow .2s ease;
}

.form-control:focus{
    outline:none;
    background:#fff;
    border-color:var(--accent);
    box-shadow:0 0 0 3px rgba(96,117,112,0.12);
}

.form-control:focus ~ i,
.input-wrap:focus-within i{
    color:var(--accent);
}

.form-control::placeholder{
    color:#94a3b8;
}

.form-meta{
    display:flex;
    align-items:center;
    justify-content:space-between;
    margin-bottom:22px;
    font-size:13px;
}

.form-check{
    display:flex;
    align-items:center;
    gap:7px;
    color:var(--muted);
}

.form-check input{
    width:15px;
    height:15px;
    accent-color:var(--accent);
    cursor:pointer;
}

.form-meta a{
    color:var(--accent);
    text-decoration:none;
    font-weight:500;
}

.form-meta a:hover{
    text-decoration:underline;
}

.login-btn{
    width:100%;
    height:46px;
    border:none;
    border-radius:10px;
    background:var(--accent);
    color:#fff;
    font-size:14px;
    font-weight:600;
    letter-spacing:.2px;
    transition:background .2s ease, transform .15s ease, box-shadow .2s ease;
    cursor:pointer;
}

.login-btn:hover{
    background:var(--accent-dark);
    box-shadow:0 6px 16px rgba(96,117,112,0.28);
}

.login-btn:active{
    transform:translateY(1px);
}

.form-footnote{
    margin-top:24px;
    text-align:center;
    font-size:13px;
    color:var(--muted);
}

.form-footnote a{
    color:var(--accent);
    font-weight:600;
    text-decoration:none;
}

.form-footnote a:hover{
    text-decoration:underline;
}

/* form crossfade on tab switch */
.login-form-anim{
    animation:formIn .35s ease both;
}

@keyframes formIn{
    from{ opacity:0; transform:translateY(6px); }
    to{ opacity:1; transform:translateY(0); }
}

.form-shell{
    animation:panelIn .5s ease both;
}

@keyframes panelIn{
    from{ opacity:0; transform:translateY(10px); }
    to{ opacity:1; transform:translateY(0); }
}

/* =========================
   RESPONSIVE
========================= */

@media(max-width:900px){
    .auth-shell{
        grid-template-columns:1fr;
    }

    .brand-panel{
        min-height:220px;
        padding:24px;
    }

    .brand-middle{
        max-width:none;
    }

    .brand-middle h1{
        font-size:22px;
        margin-bottom:8px;
    }

    .brand-middle p{
        display:none;
    }

    .brand-points{
        display:none;
    }

    .brand-bottom{
        display:none;
    }

    .form-panel{
        padding:28px 20px 40px;
    }
}

@media(max-width:480px){
    .brand-panel{
        min-height:140px;
    }

    .form-shell-head h2{
        font-size:21px;
    }
}

</style>

</head>

<body>

<div class="auth-shell">

    <!-- BRAND PANEL -->
    <div class="brand-panel">
@php
    $logoPath = \App\Classes\table::settings()->value('app_logo');
@endphp

        <div class="brand-top">
            <div class="brand-mark">
                <img src="{{ $logoPath ? asset('storage/'.$logoPath) : asset('/assets/images/img/logo.png') }}" alt="{{ __('Logo') }}"  style="width:50px;height:50px;object-fit:contain;" onerror="this.parentElement.textContent='EP'">
            </div>
            <span class="brand-name">Employee Portal</span>
        </div>

        <div class="brand-middle">
            <h1>Sign in to access your workspace</h1>
            <p>Manage your tasks, view schedules, and stay connected with your team — all in one secure place.</p>

            <ul class="brand-points">
                <li><i class="bi bi-shield-check"></i> Enterprise-grade security &amp; encrypted sessions</li>
                <li><i class="bi bi-clock-history"></i> Real-time access across all your devices</li>
                <li><i class="bi bi-headset"></i> 24/7 IT support for every employee</li>
            </ul>
        </div>

        <div class="brand-bottom">
            &copy; {{ date('Y') }} Employee Portal. All rights reserved.
        </div>

    </div>

    <!-- FORM PANEL -->
    <div class="form-panel">

        <div class="form-shell">

            <div class="form-shell-head">
                <h2>Welcome back</h2>
                <p>Enter your credentials to continue</p>
            </div>

            @if(session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-danger">
                {{ $errors->first() }}
            </div>
            @endif

            <!-- TABS -->
            <div class="login-tabs">

                <button type="button" class="active" id="emailTab" onclick="showLogin('email')">
                    Email Login
                </button>

                <button type="button" id="nidTab" onclick="showLogin('nid')">
                    National ID
                </button>

            </div>

            <!-- EMAIL LOGIN -->
            <form method="POST" action="{{ route('login') }}" id="emailLogin" class="login-form-anim">

                @csrf

                <input type="hidden" name="login_type" value="email">

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <div class="input-wrap">
                        <i class="bi bi-envelope"></i>
                        <input type="email" name="email" class="form-control" placeholder="you@company.com" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-wrap">
                        <i class="bi bi-lock"></i>
                        <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                    </div>
                </div>

                <div class="form-meta">
                    <label class="form-check">
                        <input type="checkbox" name="remember">
                        Remember me
                    </label>
                    <a href="#">Forgot password?</a>
                </div>

                <button type="submit" class="login-btn">Sign In</button>

            </form>

            <!-- NATIONAL ID LOGIN -->
            <form method="POST" action="{{ route('login') }}" id="nidLogin" style="display:none;">

                @csrf

                <input type="hidden" name="login_type" value="nid">

                <div class="form-group">
                    <label class="form-label">Passport No</label>
                    <div class="input-wrap">
                        <i class="bi bi-person-vcard"></i>
                        <input type="text" name="nationalid" class="form-control" placeholder="Enter Passport No" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Date of Birth</label>
                    <div class="input-wrap">
                        <i class="bi bi-calendar3"></i>
                        <input type="date" name="birthday" class="form-control" required>
                    </div>
                </div>

                <button type="submit" class="login-btn">Verify &amp; Sign In</button>

            </form>

            <p class="form-footnote">
                Having trouble signing in? <a href="#">Contact IT support</a>
            </p>

        </div>

    </div>

</div>

<script>

function showLogin(type){

    var emailForm = document.getElementById('emailLogin');
    var nidForm = document.getElementById('nidLogin');
    var emailTab = document.getElementById('emailTab');
    var nidTab = document.getElementById('nidTab');

    if(type === 'email'){
        emailTab.classList.add('active');
        nidTab.classList.remove('active');

        nidForm.style.display = 'none';
        emailForm.style.display = 'block';
        emailForm.classList.remove('login-form-anim');
        void emailForm.offsetWidth;
        emailForm.classList.add('login-form-anim');
    }else{
        nidTab.classList.add('active');
        emailTab.classList.remove('active');

        emailForm.style.display = 'none';
        nidForm.style.display = 'block';
        nidForm.classList.remove('login-form-anim');
        void nidForm.offsetWidth;
        nidForm.classList.add('login-form-anim');
    }
}

</script>

</body>
</html>