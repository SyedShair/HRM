<!doctype html>

<html lang="{{ app()->getLocale() }}">
    <head>
        @php
            // Branding: pulled from the Settings page (App name / logo).
            // Falls back to existing static defaults if nothing has been
            // configured yet, so this is safe even before anyone touches
            // the new fields.
            $appSettings = \App\Classes\table::settings()->where('id', 1)->first();
            $appName = !empty($appSettings->app_name) ? $appSettings->app_name : 'Jpingos';
            $appLogo = !empty($appSettings->app_logo)
                ? asset('storage/'.$appSettings->app_logo)
                : asset('/assets/images/img/logo.png');
        @endphp
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
        <meta name="viewport" content="width=device-width" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('/assets/images/img/favicon-16x16.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('/assets/images/img/favicon-32x32.png') }}">
        <link rel="icon" type="image/x-icon" href="{{ asset('/assets/images/img/favicon.ico') }}">
        
        @yield('meta')

        <link rel="stylesheet" type="text/css" href="{{ asset('/assets/vendor/bootstrap/css/bootstrap.min.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('/assets/vendor/semantic-ui/semantic.min.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('/assets/vendor/DataTables/datatables.min.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('/assets/vendor/flag-icon-css/css/flag-icon.min.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('/assets/css/style.css') }}">
        
        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
            <script src="{{ asset('/assets/vendor/html5shiv/html5shiv.min.js') }}></script>
            <script src="{{ asset('/assets/vendor/respond/respond.min.js') }}"></script>
        <![endif]-->
        
        <style>
        @media print {
    body * {
        display: none !important;
    }

    body::after {
        content: "Printing is disabled on this page.";
        display: block;
        font-size: 20px;
        text-align: center;
        margin-top: 50px;
    }
}
#visa-floating-box {
    position: fixed;
    left: 20px; /* changed from right */
    bottom: 50px;
    z-index: 9999;
    width: 320px;
    border-radius: 14px;
    padding: 16px;
    color: #fff;
    box-shadow: 0 4px 15px rgba(0,0,0,0.25);
    transition: all 0.4s ease;
    overflow: hidden;
}

#visa-floating-box.closed {
    width: 70px;
    height: 70px;
    padding: 10px;
    cursor: pointer;
}

#visa-floating-box .visa-content {
    transition: opacity 0.3s ease;
}

#visa-floating-box.closed .visa-content {
    opacity: 0;
    visibility: hidden;
}

#visa-floating-box .toggle-btn {
    position: absolute;
    top: 10px;
    right: 12px;
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    font-size: 18px;
    cursor: pointer;
}

#visa-floating-box.closed .toggle-btn {
    top: 20px;
    right: 20px;
}

.visa-mini-icon {
    display: none;
    font-size: 30px;
    text-align: center;
    margin-top: 8px;
}

#visa-floating-box.closed .visa-mini-icon {
    display: block;
}
#visa-floating-box {
    position: fixed;
    left: 20px;
    bottom: 50px;
    z-index: 9999;

    width: 320px;
    border-radius: 14px;
    padding: 16px;

    color: #fff;

    box-shadow: 0 4px 15px rgba(0,0,0,0.25);

    transition: all 0.4s ease;

    overflow: hidden;
}

/* CLOSED STATE */

#visa-floating-box.closed {
    width: 70px;
    height: 70px;
    padding: 10px;
    cursor: pointer;
}

/* CONTENT */

#visa-floating-box .visa-content {
    transition: opacity 0.3s ease;
}

#visa-floating-box.closed .visa-content {
    opacity: 0;
    visibility: hidden;
}

/* TOGGLE BUTTON */

#visa-floating-box .toggle-btn {
    position: absolute;

    top: 10px;
    left: 12px;

    background: rgba(255,255,255,0.2);

    border: none;

    color: white;

    width: 28px;
    height: 28px;

    border-radius: 50%;

    font-size: 18px;

    cursor: pointer;

    transition: 0.3s;
}

#visa-floating-box .toggle-btn:hover {
    background: rgba(255,255,255,0.35);
}

/* CLOSED BUTTON POSITION */

#visa-floating-box.closed .toggle-btn {
    top: 20px;
    left: 20px;
}

/* MINI ICON */

.visa-mini-icon {
    display: none;

    font-size: 30px;

    text-align: center;

    margin-top: 8px;
}

#visa-floating-box.closed .visa-mini-icon {
    display: block;
}

/* BLINK ANIMATION */

@keyframes blink {

    0% {
        opacity: 1;
    }

    50% {
        opacity: 0.6;
    }

    100% {
        opacity: 1;
    }
}

/* MOBILE RESPONSIVE */

@media(max-width:768px){

    #visa-floating-box{

        left: 10px;
        bottom: 10px;

        width: 280px;

        padding: 14px;
    }

    #visa-floating-box.closed{

        width: 65px;
        height: 65px;
    }

    .visa-mini-icon{

        font-size: 26px;
    }
}

@keyframes blink {
    0% { opacity:1; }
    50% { opacity:0.6; }
    100% { opacity:1; }
}

.chat-badge1{
    animation:pulse 1s infinite;
}

@keyframes pulse{

    0%{
        transform:scale(1);
    }

    50%{
        transform:scale(1.15);
    }

    100%{
        transform:scale(1);
    }
}

/* Slide in */
@keyframes slideInRight {
    from {
        transform: translateX(120%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

/* Fade out */
@keyframes fadeOut {
    to {
        opacity: 0;
        transform: translateX(120%);
    }
}

</style>
        @yield('styles')
    </head>
    <body>


        <div class="wrapper">
        
        <nav id="sidebar" class="active">
            <div class="sidebar-header bg-lightblue">
                <div class="logo">
                <a href="/" class="simple-text">
                    <img src="{{ $appLogo }}" alt="{{ $appName }}">
                </a>
                </div>
            </div>

            <ul class="list-unstyled components">
                <li class="">
                    <a href="{{ url('personal/dashboard') }}">
                        <i class="ui icon sliders horizontal"></i>
                        <p>{{ __("Dashboard") }}</p>
                    </a>
                </li>
                <li class="">
                    <a href="{{ url('personal/attendance/view') }}">
                        <i class="ui icon clock outline"></i>
                        <p>{{ __("My Attendances") }}</p>
                    </a>
                </li>
                <li class="">
                    <a href="{{ url('personal/schedules/view') }}">
                        <i class="ui icon calendar alternate outline"></i>
                        <p>{{ __("My Schedules") }}</p>
                    </a>
                </li>
                <li class="">
                    <a href="{{ url('personal/leaves/view') }}">
                        <i class="ui icon calendar plus outline"></i>
                        <p>{{ __("My Leave") }}</p>
                    </a>
                </li>
               <li>
    <a href="{{ route('personal.documents') }}">
        <i class="ui icon folder open"></i>
        <p>{{ __("Documents") }}</p>
    </a>
</li>
       
     


<li>

    <a href="{{ route('chat') }}" class="chat-menu-link1">

        <!-- ICON + BADGE -->
        <div class="chat-icon-wrapper1">

            <i class="ui comments icon"></i>

                      <span class="chat-notification-badge chat-badge1 chat-unread-badge" style="display:none;"> </span>

        </div>

        <!-- TEXT -->
        <span class="chat-text1">
            {{ __("Chat") }}
        </span>

    </a>

</li>
<li>
    <a href="{{ url('personal/clock/attendance') }}">
        <i class="ui icon clock outline"></i>
        <p>{{ __("Make Attendance") }}</p>
    </a>
</li>


<style>

/* MENU LINK */

.chat-menu-link1{
    display:flex !important;
    align-items:center !important;
    gap:10px;
    width:100%;
    text-decoration:none;
}

/* ICON WRAPPER */

.chat-icon-wrapper1{
    position:relative;
    display:flex;
    align-items:center;
    justify-content:center;
    width:22px;
}

/* ICON */

.chat-icon-wrapper1 i{
    font-size:20px;
    margin:0 !important;
}

/* TEXT */

.chat-text1{
    display:inline-block;
    line-height:1;
}

/* BADGE */

.chat-badge1{
    position:absolute;
    top:-8px;
    right:-10px;
    background:red;
    color:#fff;
    min-width:18px;
    height:18px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:10px;
    font-weight:bold;
    padding:2px 5px;
    border:2px solid #fff;
    line-height:1;
}

/* MOBILE */

@media(max-width:768px){

    .chat-menu-link{
        gap:8px;
    }

    .chat-icon-wrapper i{
        font-size:18px;
    }

    .chat-text{
        font-size:13px;
    }

}

</style>

            </ul>
        </nav>

        <div id="body" class="active">
            <nav class="navbar navbar-expand-lg navbar-light bg-lightblue">
                <div class="container-fluid">

                    <button type="button" id="slidesidebar" class="ui icon button btn-light-outline">
                        <i class="ui icon bars"></i> <span class="toggle-sidebar-menu">{{ __('Menu') }}</span>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="nav navbar-nav ml-auto navmenu">
                           


<li class="nav-item">

    <a href="{{ route('chat') }}" class="chat-nav-link">

        <!-- ICON -->
        <div class="chat-icon-wrapper">

            <i class="ui comments icon"></i>

                       <span class="chat-notification-badge chat-badge1 chat-unread-badge" style="display:none;"> </span>


        </div>

        <!-- TEXT -->
        <span class="chat-nav-text">
            {{ __('Chat') }}
        </span>

    </a>

</li>


<style>

/* NAV ITEM */

.chat-nav-link{
    display:flex !important;
    align-items:center !important;
    gap:12px;
    padding:12px 16px !important;
    text-decoration:none !important;
    border-radius:10px;
    transition:0.2s ease;
    position:relative;
}

/* HOVER */

.chat-nav-link:hover{
    background:rgba(255,255,255,0.08);
}

/* ICON WRAPPER */

.chat-icon-wrapper{
    position:relative;
    width:24px;
    display:flex;
    align-items:center;
    justify-content:center;
}

/* ICON */

.chat-icon-wrapper i{
    font-size:20px !important;
    color:#000 !important;
    margin:0 !important;
}

/* TEXT */

.chat-nav-text{
    color:#000 !important;
    font-size:15px;
    font-weight:700;
    line-height:1;
    letter-spacing:0.3px;
    display:flex;
    align-items:center;
}

/* BADGE */

.chat-notification-badge{
    position:absolute;
    top:-7px;
    right:-10px;
    background:#ff3b30;
    color:#fff;
    min-width:20px;
    height:20px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:11px;
    font-weight:700;
    border:2px solid #fff;
    padding:2px 6px;
    box-shadow:0 2px 6px rgba(0,0,0,0.2);
}

/* MOBILE */

@media(max-width:768px){

    .chat-nav-link{
        gap:8px;
        padding:10px 12px !important;
    }

    .chat-nav-text{
        font-size:13px;
    }

    .chat-icon-wrapper i{
        font-size:18px !important;
    }

    .chat-notification-badge{
        min-width:17px;
        height:17px;
        font-size:9px;
        top:-5px;
        right:-8px;
    }

}

            /* ================================
   LARGE + RESPONSIVE SIDEBAR LOGO
   ================================ */

#sidebar .sidebar-header {
    width: 100%;
    height: 95px;
    padding: 10px 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

#sidebar .logo {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

#sidebar .logo a.simple-text {
    width: 100%;
    height: 100%;
    display: flex !important;
    align-items: center;
    justify-content: center;
    padding: 0 !important;
    margin: 0 !important;
}

#sidebar .sidebar-logo {
    display: block;
    width: 100%;
    max-width: 210px;
    height: auto;
    max-height:100%;
    object-fit: contain;
    object-position: center;
}

/* Expanded sidebar */
#sidebar:not(.active) .sidebar-logo {
    max-width: 210px;
    max-height: 75px;
}

/* Collapsed sidebar */
#sidebar.active .sidebar-header {
    height: 80px;
    padding: 8px;
}

#sidebar.active .sidebar-logo {
    width: 65px;
    max-width: 65px;
    max-height: 65px;
}

/* Tablet */
@media (max-width: 768px) {
    #sidebar .sidebar-header {
        height: 85px;
        padding: 8px 10px;
    }

    #sidebar .sidebar-logo {
        max-width: 180px;
        max-height: 65px;
    }

    #sidebar.active .sidebar-logo {
        width: 60px;
        max-width: 60px;
        max-height: 60px;
    }
}

/* Mobile */
@media (max-width: 480px) {
    #sidebar .sidebar-header {
        height: 75px;
    }

    #sidebar .sidebar-logo {
        max-width: 160px;
        max-height: 58px;
    }

    #sidebar.active .sidebar-logo {
        width: 55px;
        max-width: 55px;
        max-height: 55px;
    }
}

</style>
                            <li class="nav-item">
                                <div class="ui pointing link dropdown item" tabindex="0">
                                    <i class="ui icon flag"></i> <span class="navmenutext uppercase">{{ env('APP_LOCALE', 'en') }}</span>
                                    <i class="dropdown icon"></i>
                                    <div class="menu" tabindex="-1">
                                      <a href="{{ url('lang/en') }}" class="item"><i class="flag-icon flag-icon-us"></i>English</a>
                                      <a href="{{ url('lang/es') }}" class="item"><i class="flag-icon flag-icon-es"></i>Español</a>
                                      <a href="{{ url('lang/fr') }}" class="item"><i class="flag-icon flag-icon-fr"></i>Français</a>
                                      <a href="{{ url('lang/de') }}" class="item"><i class="flag-icon flag-icon-de"></i>Deutsch</a>
                                      <a href="{{ url('lang/jp') }}" class="item"><i class="flag-icon flag-icon-jp"></i>日本語</a>
                                      <a href="{{ url('lang/in') }}" class="item"><i class="flag-icon flag-icon-in"></i>Hindi</a>
                                      <a href="{{ url('lang/it') }}" class="item"><i class="flag-icon flag-icon-it"></i>Italian</a>
                                      <a href="{{ url('lang/kr') }}" class="item"><i class="flag-icon flag-icon-kr"></i>한국말</a>
                                      <a href="{{ url('lang/my') }}" class="item"><i class="flag-icon flag-icon-my"></i>Malay</a>
                                      <a href="{{ url('lang/nl') }}" class="item"><i class="flag-icon flag-icon-nl"></i>Dutch</a>
                                      <a href="{{ url('lang/ph') }}" class="item"><i class="flag-icon flag-icon-ph"></i>Filipino</a>
                                      <a href="{{ url('lang/pt') }}" class="item"><i class="flag-icon flag-icon-pt"></i>Português</a>
                                    </div>
                              </div>
                            </li>
                            <li class="nav-item">
                                <div class="ui pointing link dropdown item" tabindex="0">
                                    <i class="ui icon linkify"></i> <span class="navmenutext uppercase">{{ __("Quick Access") }}</span>
                                    <i class="dropdown icon"></i>
                                    <div class="menu" tabindex="-1">
                                      <a href="{{ url('personal/clock/attendance') }}" target="_blank" class="item"><i class="ui icon clock outline"></i> {{ __("Clock In/Out") }}</a>
                                      <div class="divider"></div>
                                      <a href="{{ url('personal/profile/view') }}" target="_blank" class="item"><i class="ui icon user outline"></i> {{ __("My Profile") }}</a>
                                    </div>
                              </div>
                            </li>
                            <li class="nav-item">
                               <div class="ui pointing link dropdown item" tabindex="0">
                                    <i class="ui icon user outline"></i><span class="navmenutext">@isset(Auth::user()->name) {{ Auth::user()->name }} @endisset</span>
                                    <i class="dropdown icon"></i>
                                    <div class="menu" tabindex="-1">
                                      <a href="{{ url('personal/update-user') }}" class="item"><i class="ui icon user"></i> {{ __("Update User") }}</a>
                                      <a href="{{ url('personal/update-password') }}" class="item"><i class="ui icon lock"></i> {{ __("Change Password") }}</a>
                                      <div class="divider"></div>
                                      <a href="{{ url('logout') }}" class="item"><i class="ui icon power"></i> {{ __("Logout") }}</a>
                                    </div>
                                </div>
                            </li>

                        </ul>
                    </div>
                </div>
            </nav>

            <div class="content">
                @yield('content')
            </div>

            <input type="hidden" id="_url" value="{{url('/')}}">
            <script>
                var y = '@isset($var){{$var}}@endisset';
            </script>
        </div>
    </div>

    <script src="{{ asset('/assets/vendor/jquery/jquery-3.4.1.min.js') }}"></script>
    <script src="{{ asset('/assets/vendor/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('/assets/vendor/semantic-ui/semantic.min.js') }}"></script>
    <script src="{{ asset('/assets/vendor/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
    <script src="{{ asset('/assets/vendor/DataTables/datatables.min.js') }}"></script>
    <script src="{{ asset('/assets/js/script.js') }}"></script>
    @if ($success = Session::get('success'))
    <script>
        $(document).ready(function() {
            $.notify({icon: 'ti-check',message: "{{ $success }}"},{type: 'success',timer: 600});
        });
    </script>
    @endif

    @if ($error = Session::get('error'))
    <script>
        $(document).ready(function() {
            $.notify({icon: 'ti-close',message: "{{ $error }}"},{type: 'danger',timer: 600});
        });
    </script>
    @endif
<script>

$(document).ready(function () {

    // INITIAL LOAD
    loadUnreadMessages();

    // AUTO REFRESH EVERY 2 SECONDS
    setInterval(function () {

        loadUnreadMessages();

    }, 2000);

});

/*
|--------------------------------------------------------------------------
| LOAD UNREAD MESSAGE COUNT
|--------------------------------------------------------------------------
*/

function loadUnreadMessages()
{
    $.ajax({

        url: '/chat/unread-count',

        type: 'GET',

        dataType: 'json',

        cache: false,

        success: function (response)
        {
            let count = response.count;

            // UPDATE ALL BADGES

            $('.chat-unread-badge').each(function () {

                if(count > 0)
                {
                    $(this)
                        .text(count)
                        .fadeIn(200);
                }
                else
                {
                    $(this)
                        .fadeOut(200);
                }

            });
        },

        error: function (xhr, status, error)
        {
            console.log('Unread count load failed');
            console.log(error);
        }

    });
}

</script>

    @yield('scripts')

    </body>
</html>