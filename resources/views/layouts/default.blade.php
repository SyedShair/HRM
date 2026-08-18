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
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('/assets/images/img/favicon-16x16.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('/assets/images/img/favicon-32x32.png') }}">
        <link rel="icon" type="image/x-icon" href="{{ asset('/assets/images/img/favicon.ico') }}">

        @yield('meta')

        <link rel="stylesheet" type="text/css" href="{{ asset('/assets/vendor/bootstrap/css/bootstrap.min.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('/assets/vendor/semantic-ui/semantic.min.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('/assets/vendor/DataTables/datatables.min.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('/assets/vendor/flag-icon-css/css/flag-icon.min.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('/assets/css/style.css') }}">

        {{-- HTML5 Shim and Respond.js: IE8 support for HTML5 elements and media queries.
             WARNING: Respond.js doesn't work if you view the page via file://
             Kept as a Blade comment (server-stripped) rather than a raw HTML
             comment so it can never leak into the rendered page if nested or
             modified incorrectly. --}}
        <!--[if lt IE 9]>
            <script src="{{ asset('/assets/vendor/html5shiv/html5shiv.min.js') }}"></script>
            <script src="{{ asset('/assets/vendor/respond/respond.min.js') }}"></script>
        <![endif]-->

        @yield('styles')

        <style>
        :root {
            --primary: #0f172a;      /* dark navy */
            --secondary: #14b8a6;    /* teal */
            --bg: #f8fafc;           /* light background */
            --card: #ffffff;
            --border: #e2e8f0;
            --text: #1e293b;
        }

            body {
                background-color: #f8f9fa;
                color: #495057;
                font-size: 14px;
            }

            #sidebar {
                background: #ffffff;
                border-right: 1px solid var(--border);
            }

            #sidebar .sidebar-header {
                background: #ffffff;
                border-bottom: 1px solid var(--border);
            }

            #sidebar ul li a {
                color: #64748b;
            }

            #sidebar ul li a:hover,
            #sidebar ul li a:hover i {
                color: var(--secondary);
                background: rgba(20,184,166,0.10);
            }

            #sidebar ul li.active>a,
            a[aria-expanded="true"] {
                color: #fff;
                background: var(--secondary);
            }

            #sidebar ul li a i {
                color: #64748b;
            }

            .navbar {
                background: #0f172a !important;
                border-bottom: 1px solid var(--border);
                position: relative;
                z-index: 1050;
            }

            .btn-light-outline,
            .btn-light-outline:hover,
            .btn-light-outline:focus,
            .btn-light-outline:active {
                color: #fff !important;
                box-shadow: 0 0 0 1px rgba(255,255,255,0.5) inset !important;
                background: transparent none !important;
            }

            .btn-light-outline:hover,
            .btn-light-outline:active {
                background: rgba(255,255,255,0.1) !important;
            }

            .ui.pointing.dropdown>.menu {
                margin-top: 0 !important;
                background: #fff;
                border: 1px solid var(--border);
                z-index: 9999 !important;
            }

            .ui.dropdown .menu>.item {
                color: #495057 !important;
            }

            .ui.dropdown .menu>.item:hover {
                background: rgba(20,184,166,0.10) !important;
                color: var(--secondary) !important;
            }

            .navmenutext {
                color: #fff;
            }

            .ui.icon {
                color: #fff;
            }

            .page-title {
                color: var(--secondary);
            }

            .box {
                background: #fff;
                border: 1px solid var(--border);
            }

            .box-header {
                border-bottom: 1px solid var(--border);
            }

            .box-footer {
                background-color: #f8f9fa;
                border-top: 1px solid var(--border);
            }

            .info-box {
                background: #fff;
            }

            .bg-aqua {
                background-color: var(--secondary) !important;
            }

            .bg-green {
                background-color: #28a745 !important;
            }

            .bg-orange {
                background-color: #fd7e14 !important;
            }

            .ui.menu .item {
                color: #64748b !important;
            }

            .ui.menu .active.item {
                color: var(--secondary) !important;
                font-weight: normal !important;
            }

            .ui.table {
                border: 1px solid var(--border);
            }

            .ui.table thead th {
                background: #f8f9fa;
                color: #495057;
            }

            .ui.pagination.menu .active.item {
                background-color: var(--secondary) !important;
                color: #fff !important;
            }

            a {
                color: var(--secondary);
            }

            a:hover {
                color: #0d9488;
            }

            .ui.button.primary {
                background-color: var(--secondary) !important;
                color: #fff !important;
            }

            .ui.button.primary:hover {
                background-color: #0d9488 !important;
            }

            .ui.form input:focus,
            .ui.form textarea:focus {
                border-color: var(--secondary) !important;
            }

            .positive {
                color: #28a745;
            }

            .negative {
                color: #dc3545;
            }

            /* Animated gradient navbar */
            .navbar {
                background: linear-gradient(120deg, #0d9488, var(--secondary), #2dd4bf) !important;
                background-size: 220% 220% !important;
                animation: gradientShift 10s ease infinite;
            }

            @keyframes gradientShift {
                0%   { background-position: 0% 50%; }
                50%  { background-position: 100% 50%; }
                100% { background-position: 0% 50%; }
            }

            .chat-badge1 {
                animation: pulse 1s infinite;
            }

            @keyframes pulse {
                0%   { transform: scale(1); }
                50%  { transform: scale(1.15); }
                100% { transform: scale(1); }
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
    </head>
    <body>

        <div class="wrapper">

        <nav id="sidebar" class="active" style="background: #ffffff; border-right: 1px solid var(--border);">
           
<div class="sidebar-header">
    <div class="logo">
        <a href="/" class="simple-text">
            <img
                src="{{ $appLogo }}"
                alt="{{ $appName }}"
                class="sidebar-logo"
            >
        </a>
    </div>
</div>
            <ul class="list-unstyled components">
                <li class="">
                    <a href="{{ url('dashboard') }}" style="color: #64748b;">
                        <i class="ui icon sliders horizontal" style="color: #64748b;"></i>
                        <p>{{ __('Dashboard') }}</p>
                    </a>
                </li>

                <li class="">
                    <a href="{{ url('employees') }}" style="color: #64748b;">
                        <i class="ui icon users" style="color: #64748b;"></i>
                        <p>{{ __('Employees') }}</p>
                    </a>
                </li>

                <li class="">
                    <a href="{{ url('attendance') }}" style="color: #64748b;">
                        <i class="ui icon clock outline" style="color: #64748b;"></i>
                        <p>{{ __('Attendances') }}</p>
                    </a>
                </li>

                <li class="">
                    <a href="{{ url('schedules') }}" style="color: #64748b;">
                        <i class="ui icon calendar alternate outline" style="color: #64748b;"></i>
                        <p>{{ __('Schedules') }}</p>
                    </a>
                </li>
                <li class="">
                    <a href="{{ url('staff-rota') }}" style="color: #64748b;">
                        <i class="ui icon clipboard list" style="color: #64748b;"></i>
                        <p>{{ __('Staff Rota') }}</p>
                    </a>
                </li>
                <li class="">
                    <a href="{{ url('today-shifts') }}" style="color: #64748b;">
                        <i class="ui icon clock outline" style="color: #64748b;"></i>
                        <p>{{ __('Today Shifts') }}</p>
                    </a>
                </li>

                <li class="">
                    <a href="{{ url('leaves') }}" style="color: #64748b;">
                        <i class="ui icon calendar plus outline" style="color: #64748b;"></i>
                        <p>{{ __('Leave') }}</p>
                    </a>
                </li>
                <li class="">
                    <a href="{{ url('reports') }}" style="color: #64748b;">
                        <i class="ui icon chart bar outline" style="color: #64748b;"></i>
                        <p>{{ __('Reports') }}</p>
                    </a>
                </li>
                <li>
                    <a href="{{ url('users') }}" style="color: #64748b;">
                        <i class="ui icon user circle outline" style="color: #64748b;"></i>
                        <p>{{ __('Users') }}</p>
                    </a>
                </li>
                 <li>
                    <a href="{{ url('payroll') }}" style="color: #64748b;">
                        <i class="ui icon money bill alternate outline" style="color: #64748b;"></i>
                        <p>{{ __('Payroll') }}</p>
                    </a>
                </li>
                <li>
                    <a href="{{ route('chat') }}" class="chat-menu-link1" style="color: #64748b;">
                        <div class="chat-icon-wrapper1">
                            <i class="ui comments icon" style="color: #64748b;"></i>
                            <span class="chat-notification-badge chat-badge1 chat-unread-badge" style="display:none;"> </span>
                        </div>
                        <span class="chat-text1">{{ __('Chat') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ url('settings') }}" style="color: #64748b;">
                        <i class="ui icon cog" style="color: #64748b;"></i>
                        <p>{{ __('Settings') }}</p>
                    </a>
                </li>
            </ul>
        </nav>

        <style>
            .chat-menu-link1 {
                display:flex !important;
                align-items:center !important;
                gap:10px;
                width:100%;
                text-decoration:none;
            }

            .chat-icon-wrapper1 {
                position:relative;
                display:flex;
                align-items:center;
                justify-content:center;
                width:22px;
            }

            .chat-icon-wrapper1 i {
                font-size:20px;
                margin:0 !important;
            }

            .chat-text1 {
                display:inline-block;
                line-height:1;
            }

            .chat-badge1 {
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

            .chat-nav-link {
                display:flex !important;
                align-items:center !important;
                gap:12px;
                padding:12px 16px !important;
                text-decoration:none !important;
                border-radius:10px;
                transition:0.2s ease;
                position:relative;
            }

            .chat-nav-link:hover {
                background:rgba(255,255,255,0.08);
            }

            .chat-icon-wrapper {
                position:relative;
                width:24px;
                display:flex;
                align-items:center;
                justify-content:center;
            }

            .chat-icon-wrapper i {
                font-size:20px !important;
                color:#fff !important;
                margin:0 !important;
            }

            .chat-nav-text {
                color:#fff !important;
                font-size:15px;
                font-weight:700;
                line-height:1;
                letter-spacing:0.3px;
                display:flex;
                align-items:center;
            }

            .chat-notification-badge {
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
                box-shadow:0 2px 6px rgba(0,0,0,0.2);
            }

            @media (max-width:768px) {
                .chat-nav-link { gap:8px; padding:10px 12px !important; }
                .chat-nav-text { font-size:13px; }
                .chat-icon-wrapper i { font-size:18px !important; }
                .chat-notification-badge { min-width:17px; height:17px; font-size:9px; top:-5px; right:-8px; }
            }
        </style>

        <div id="body" class="active">
            <nav class="navbar navbar-expand-lg navbar-light" style="border-bottom: 1px solid var(--border); position: relative; z-index: 1050;">
                <div class="container-fluid">

                    <button type="button" id="slidesidebar" class="ui icon button btn-light-outline" style="color: #fff !important; box-shadow: 0 0 0 1px rgba(255,255,255,0.5) inset !important;">
                        <i class="ui icon bars" style="color: #fff;"></i> <span class="toggle-sidebar-menu" style="color: #fff;">{{ __('Menu') }}</span>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="nav navbar-nav ml-auto navmenu">
                            <li class="nav-item">
                                <a href="{{ route('chat') }}" class="chat-nav-link">
                                    <div class="chat-icon-wrapper">
                                        <i class="ui comments icon"></i>
                                        <span class="chat-notification-badge chat-badge1 chat-unread-badge" style="display:none;"> </span>
                                    </div>
                                    <span class="chat-nav-text">{{ __('Chat') }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <div class="ui pointing link dropdown item" tabindex="0" style="color: #fff;">
                                    <i class="ui icon flag" style="color: #fff;"></i> <span class="navmenutext uppercase" style="color: #fff;">{{ config('app.locale', 'en') }}</span>
                                    <i class="dropdown icon" style="color: #fff;"></i>
                                    <div class="menu" tabindex="-1" style="background: #fff; border: 1px solid var(--border);">
                                      <a href="{{ url('lang/en') }}" class="item" style="color: #495057 !important;"><i class="flag-icon flag-icon-us"></i>English</a>
                                      <a href="{{ url('lang/es') }}" class="item" style="color: #495057 !important;"><i class="flag-icon flag-icon-es"></i>Español</a>
                                      <a href="{{ url('lang/fr') }}" class="item" style="color: #495057 !important;"><i class="flag-icon flag-icon-fr"></i>Français</a>
                                      <a href="{{ url('lang/de') }}" class="item" style="color: #495057 !important;"><i class="flag-icon flag-icon-de"></i>Deutsch</a>
                                      <a href="{{ url('lang/jp') }}" class="item" style="color: #495057 !important;"><i class="flag-icon flag-icon-jp"></i>日本語</a>
                                      <a href="{{ url('lang/in') }}" class="item" style="color: #495057 !important;"><i class="flag-icon flag-icon-in"></i>Hindi</a>
                                      <a href="{{ url('lang/it') }}" class="item" style="color: #495057 !important;"><i class="flag-icon flag-icon-it"></i>Italian</a>
                                      <a href="{{ url('lang/kr') }}" class="item" style="color: #495057 !important;"><i class="flag-icon flag-icon-kr"></i>한국말</a>
                                      <a href="{{ url('lang/my') }}" class="item" style="color: #495057 !important;"><i class="flag-icon flag-icon-my"></i>Malay</a>
                                      <a href="{{ url('lang/nl') }}" class="item" style="color: #495057 !important;"><i class="flag-icon flag-icon-nl"></i>Dutch</a>
                                      <a href="{{ url('lang/ph') }}" class="item" style="color: #495057 !important;"><i class="flag-icon flag-icon-ph"></i>Filipino</a>
                                      <a href="{{ url('lang/pt') }}" class="item" style="color: #495057 !important;"><i class="flag-icon flag-icon-pt"></i>Português</a>
                                    </div>
                              </div>
                            </li>
                            <li class="nav-item">
                                <div class="ui pointing link dropdown item" tabindex="0" style="color: #fff;">
                                    <i class="ui icon linkify" style="color: #fff;"></i> <span class="navmenutext uppercase" style="color: #fff;">{{ __('Quick Access') }}</span>
                                    <i class="dropdown icon" style="color: #fff;"></i>
                                    <div class="menu" tabindex="-1" style="background: #fff; border: 1px solid var(--border);">
                                      <a href="{{ url('clock') }}" target="_blank" rel="noopener noreferrer" class="item" style="color: #495057 !important;"><i class="ui icon clock outline"></i>{{ __('Clock In/Out') }}</a>
                                      <div class="divider"></div>
                                      <a href="{{ url('employees/new') }}" class="item" style="color: #495057 !important;"><i class="ui icon user plus"></i>{{ __('New Employee') }}</a>
                                      <div class="divider"></div>
                                      <a href="{{ url('fields/company') }}" class="item" style="color: #495057 !important;"><i class="ui icon university"></i>{{ __('Company') }}</a>
                                      <a href="{{ url('fields/department') }}" class="item" style="color: #495057 !important;"><i class="ui icon cubes"></i>{{ __('Department') }}</a>
                                      <a href="{{ url('fields/jobtitle') }}" class="item" style="color: #495057 !important;"><i class="ui icon pencil alternate"></i>{{ __('Job Title') }}</a>
                                      <a href="{{ url('fields/leavetype') }}" class="item" style="color: #495057 !important;"><i class="ui icon calendar alternate outline"></i>{{ __('Leave Type') }}</a>
                                    </div>
                              </div>
                            </li>
                            <li class="nav-item">
                               <div class="ui pointing link dropdown item" tabindex="0" style="color: #fff;">
                                    <i class="ui icon user outline" style="color: #fff;"></i> <span class="navmenutext" style="color: #fff;">@isset(Auth::user()->name){{ Auth::user()->name }}@endisset</span>
                                    <i class="dropdown icon" style="color: #fff;"></i>
                                    <div class="menu" tabindex="-1" style="background: #fff; border: 1px solid var(--border);">
                                      <a href="{{ url('update-profile') }}" class="item" style="color: #495057 !important;"><i class="ui icon user"></i>{{ __('Update Account') }}</a>
                                      <a href="{{ url('update-password') }}" class="item" style="color: #495057 !important;"><i class="ui icon lock"></i>{{ __('Change Password') }}</a>
                                      <a href="{{ url('personal/dashboard') }}" target="_blank" rel="noopener noreferrer" class="item" style="color: #495057 !important;"><i class="ui icon sign-in"></i>{{ __('Switch to MyAccount') }}</a>
                                      <div class="divider"></div>
                                      <a href="{{ url('logout') }}" class="item" style="color: #495057 !important;"><i class="ui icon power"></i>{{ __('Logout') }}</a>
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

    {{--
        Global Semantic UI dropdown init (top-nav language / quick access / user menu).

        SCOPING NOTE: this used to run $('.ui.dropdown').dropdown(...) against
        EVERY .ui.dropdown on every page, with no onChange handler. Any page
        that needed its own dropdown config (e.g. an onChange callback) had
        that config silently overwritten, because this script's $(document).ready()
        callback is registered here in the layout — before any page-level
        @yield('scripts') content — so it always runs LAST in the ready queue
        and wins.

        Pages that need custom dropdown behavior should add the
        "no-global-init" class to that specific dropdown element; this
        selector explicitly skips those, so page-specific JS (registered in
        @yield('scripts'), also wrapped in its own $(document).ready()) is
        free to configure them without being clobbered.
    --}}
    <script>
    $(document).ready(function () {
        $('.ui.dropdown').not('.no-global-init').dropdown({
            on: 'click',
            action: 'activate'
        });
    });
    </script>

    @if ($success = Session::get('success'))
    <script>
        $(document).ready(function() {
            $.notify({
                icon: 'ui icon check',
                message: {!! Js::from($success) !!}},
                {type: 'success',timer: 400}
            );
        });
    </script>
    @endif

    @if ($error = Session::get('error'))
    <script>
        $(document).ready(function() {
            $.notify({
                icon: 'ui icon times',
                message: {!! Js::from($error) !!}},
                {type: 'danger',timer: 400});
        });
    </script>
    @endif

    <script>
    $(document).ready(function () {
        loadUnreadMessages();

        var chatPollTimer = setInterval(function () {
            loadUnreadMessages();
        }, 15000);

        // Pause polling while the tab is hidden so background tabs don't
        // keep hitting the server every few seconds; resume on return.
        document.addEventListener('visibilitychange', function () {
            if (document.hidden) {
                clearInterval(chatPollTimer);
            } else {
                loadUnreadMessages();
                chatPollTimer = setInterval(loadUnreadMessages, 15000);
            }
        });
    });

    function loadUnreadMessages() {
        $.ajax({
            url: '/chat/unread-count',
            type: 'GET',
            dataType: 'json',
            cache: false,
            success: function (response) {
                let count = response.count;
                $('.chat-unread-badge').each(function () {
                    if (count > 0) {
                        $(this).text(count).fadeIn(200);
                    } else {
                        $(this).fadeOut(200);
                    }
                });
            },
            error: function (xhr, status, error) {
                console.log('Unread count load failed');
                console.log(error);
            }
        });
    }
    </script>

     
    @yield('scripts')

    </body>
</html>