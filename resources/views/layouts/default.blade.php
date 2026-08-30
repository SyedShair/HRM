<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        @php
            // Branding: pulled from the Settings page (App name / logo).
            // Falls back to existing static defaults if nothing has been
            // configured yet, so this is safe even before anyone touches
            // the new fields.
            $appSettings = \App\Classes\table::settings()->where('id', 1)->first();
            $appName = !empty($appSettings->app_name) ? $appSettings->app_name : 'Comapny';
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
    --bodyColor: #222222;
    --bodyBg: #fff;
    --whiteColor: #ffffff;
    --blackColor: #0A0624;
    --blackBlue: #13201C;
    --headingColor: #222222;
    --contentColor: #5F6C76;
    --primaryColor: #607570;
    --secondaryColor: #3E5B54;
    --greyColor: #EDEDED;
    --borderColor: #eeeeee;
    --borderColor2: #D7E1DC;
    --borderColor3: #ccc;
    --navyBlue: #3E5B54;
    --navyBlue2: #2B3D37;
    --pinkcolor: #EEF3F1;
    --violet: #7C948E;
    --yellow: #C9A227;
    --gradientColor: linear-gradient(90deg, #3E5B54 0%, #4F6B63 47.92%, #607570 100%);
    --gradientColor2: linear-gradient(180deg, rgba(19, 32, 28, 0.00) 0%, #13201C 100%);
    --gradientColor3: linear-gradient(90deg, #3E5B54 0%, rgba(255, 255, 255, 0.00) 100%);
    --borderRadius: 8px;
    --borderRadius2: 5px;
    --transition: .3s;
    --bodyFont: "Inter", sans-serif;
    --headingFont: "Inter", sans-serif;
    --contentFont: "Inter", sans-serif;
    --buttonFont: "Source Sans Pro";
}

            body {
                background-color: #f8f9fa;
                color: #495057;
                font-size: 14px;
            }

            /* ================================================
               SIDEBAR — container
               ================================================ */

            #sidebar {
                background: #ffffff;
                border-right: 1px solid var(--borderColor);
            }

            #sidebar .sidebar-header {
                background: #ffffff;
                border-bottom: 1px solid var(--borderColor);
            }

            #sidebar ul.components {
                padding: 14px 0 24px;
            }

            /* Custom slim scrollbar for the sidebar, since the list can
               get long with all the submenus expanded. */
            #sidebar {
                scrollbar-width: thin;
                scrollbar-color: #d7e1dc transparent;
            }
            #sidebar::-webkit-scrollbar {
                width: 6px;
            }
            #sidebar::-webkit-scrollbar-thumb {
                background: #d7e1dc;
                border-radius: 10px;
            }
            #sidebar::-webkit-scrollbar-thumb:hover {
                background: var(--primaryColor);
            }

            /* ================================================
               SIDEBAR — nav items
               ================================================ */

            #sidebar ul li a {
                color: #33404a;
            }

            #sidebar ul.components > li {
                margin: 3px 12px;
            }

            /* Idle state now gets its own light background + border so
               each item reads as a distinct "button" sitting on the white
               panel, instead of bare text that only appears on hover. */
            #sidebar ul.components > li > a,
            #sidebar ul.components > li.sidebar-dropdown > a {
                display: flex;
                align-items: center;
                gap: 13px;
                padding: 11px 14px;
                border-radius: 12px;
                font-weight: 500;
                font-size: 14px;
                letter-spacing: 0.01em;
                background: #f7f9f8;
                border: 1px solid #edf1ef;
                transition: background var(--transition) ease, border-color var(--transition) ease, transform var(--transition) ease, box-shadow var(--transition) ease;
                position: relative;
            }

            #sidebar ul.components > li > a p {
                margin: 0;
                line-height: 1.2;
            }

            /* Icon badge: every sidebar icon sits inside a small rounded
               square with its own subtle background, instead of a bare
               glyph floating next to the text - this is what makes the
               list read as a set of "buttons" rather than a plain link
               list. */
            #sidebar ul.components > li > a i.ui.icon,
            #sidebar ul.components > li.sidebar-dropdown > a i.ui.icon {
                width: 32px;
                height: 32px;
                min-width: 32px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 !important;
                border-radius: 9px;
                background: rgba(62, 91, 84, 0.14);
                color: var(--secondaryColor) !important;
                font-size: 15px !important;
                transition: background var(--transition) ease, color var(--transition) ease, transform var(--transition) ease;
            }

            /* The little chevron caret on dropdown toggles isn't part of
               the icon-badge system above - keep it plain and just let
               it rotate (existing behavior). */
            #sidebar ul.components > li.sidebar-dropdown > a i.sidebar-caret {
                width: auto;
                height: auto;
                min-width: 0;
                background: transparent !important;
                color: #9aa5ad !important;
                font-size: 11px !important;
            }

            #sidebar ul li a:hover,
            #sidebar ul li a:hover i {
                color: var(--secondaryColor);
            }

            #sidebar ul.components > li > a:hover,
            #sidebar ul.components > li.sidebar-dropdown > a:hover {
                background: rgba(96, 117, 112, 0.16);
                border-color: rgba(96, 117, 112, 0.35);
                transform: translateX(3px);
            }

            #sidebar ul.components > li > a:hover i.ui.icon,
            #sidebar ul.components > li.sidebar-dropdown > a:hover i.ui.icon {
                background: var(--secondaryColor);
                color: #fff !important;
                transform: scale(1.06);
            }

            #sidebar ul li a i {
                color: #64748b;
            }

            /* Active / expanded state - a filled gradient pill with a
               soft shadow so the current section is unmistakable at a
               glance, beating any inline or default icon color. */
            #sidebar ul li.active > a,
            #sidebar ul li.active > a i,
            #sidebar ul li.active > a p,
            a[aria-expanded="true"],
            a[aria-expanded="true"] i,
            a[aria-expanded="true"] .sidebar-caret {
                color: #fff !important;
            }

            #sidebar ul li.active > a,
            a[aria-expanded="true"] {
                background: var(--gradientColor) !important;
                box-shadow: 0 6px 16px rgba(62, 91, 84, 0.28);
            }

            #sidebar ul li.active > a i.ui.icon,
            a[aria-expanded="true"] i.ui.icon {
                background: rgba(255, 255, 255, 0.18) !important;
                color: #fff !important;
            }

            a[aria-expanded="true"] .sidebar-caret {
                color: #fff !important;
            }

            .navbar {
                background: #0f172a !important;
                border-bottom: 1px solid var(--borderColor);
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
                border: 1px solid var(--borderColor);
                z-index: 9999 !important;
            }

            .ui.dropdown .menu>.item {
                color: #495057 !important;
            }

            .ui.dropdown .menu>.item:hover {
                background: rgba(96,117,112,0.10) !important;
                color: var(--secondaryColor) !important;
            }

            .navmenutext {
                color: #fff;
            }

            .ui.icon {
                color: #fff;
            }

            .page-title {
                color: var(--secondaryColor);
            }

            .box {
                background: #fff;
                border: 1px solid var(--borderColor);
            }

            .box-header {
                border-bottom: 1px solid var(--borderColor);
            }

            .box-footer {
                background-color: #f8f9fa;
                border-top: 1px solid var(--borderColor);
            }

            .info-box {
                background: #fff;
            }

            .bg-aqua {
                background-color: var(--secondaryColor) !important;
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
                color: var(--secondaryColor) !important;
                font-weight: normal !important;
            }

            .ui.table {
                border: 1px solid var(--borderColor);
            }

            .ui.table thead th {
                background: #f8f9fa;
                color: #495057;
            }

            .ui.pagination.menu .active.item {
                background-color: var(--secondaryColor) !important;
                color: #fff !important;
            }

            a {
                color: var(--secondaryColor);
            }

            a:hover {
                color: #2B3D37;
            }

            .ui.button.primary {
                background-color: var(--secondaryColor) !important;
                color: #fff !important;
            }

            .ui.button.primary:hover {
                background-color: #2B3D37 !important;
            }

            .ui.form input:focus,
            .ui.form textarea:focus {
                border-color: var(--secondaryColor) !important;
            }

            .positive {
                color: #28a745;
            }

            .negative {
                color: #dc3545;
            }

            /* Animated gradient navbar */
            .navbar {
                background: linear-gradient(120deg, #2B3D37, var(--secondaryColor), var(--primaryColor)) !important;
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

/* Collapsed rail: center the icon badges, hide the text label, and
   drop the hover slide-over-to-the-right effect (nothing to slide
   toward against a bare icon rail). */
#sidebar.active ul.components > li > a,
#sidebar.active ul.components > li.sidebar-dropdown > a {
    justify-content: center;
    padding: 11px 8px;
}

#sidebar.active ul.components > li > a p {
    display: none;
}

#sidebar.active ul.components > li > a:hover,
#sidebar.active ul.components > li.sidebar-dropdown > a:hover {
    transform: none;
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

            /* ================================
               SIDEBAR "QUICK ACCESS" DROPDOWN
               (Bootstrap collapse - reuses the
               a[aria-expanded="true"] rule above
               for the toggle link's own highlight)
               ================================ */

            .sidebar-dropdown > a {
                display: flex;
                align-items: center;
                width: 100%;
            }

            .sidebar-dropdown > a .sidebar-caret {
                margin-left: auto;
                font-size: 11px !important;
                transition: transform var(--transition) ease;
            }

            .sidebar-dropdown > a[aria-expanded="true"] .sidebar-caret {
                transform: rotate(180deg);
            }

            ul.sidebar-submenu {
                background: #f8fafb;
                margin: 4px 12px 6px;
                padding: 4px 0;
                list-style: none;
                overflow: hidden;
                border-radius: 10px;
                border: 1px solid #eef2f0;
            }

            ul.sidebar-submenu li a {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 9px 12px 9px 16px !important;
                font-size: 13px;
                color: #64748b;
                text-decoration: none;
                white-space: nowrap;
                border-radius: 8px;
                margin: 2px 6px;
                transition: background var(--transition) ease, color var(--transition) ease;
            }

            ul.sidebar-submenu li a i {
                font-size: 12px !important;
                color: #8b96a0;
                margin: 0 !important;
                flex: 0 0 16px;
                text-align: center;
                transition: color var(--transition) ease;
            }

            ul.sidebar-submenu li a p {
                margin: 0;
                text-align: left;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            ul.sidebar-submenu li a:hover,
            ul.sidebar-submenu li a:hover i {
                color: var(--secondaryColor);
                background: rgba(96,117,112,0.10);
            }

            /* Icon-only collapsed sidebar: the flyout submenu doesn't
               make sense against a 65px icon rail, so keep the section
               closed and hide the caret rather than show a broken
               inline list. */
            #sidebar.active .sidebar-dropdown > a .sidebar-caret,
            #sidebar.active ul.sidebar-submenu {
                display: none;
            }
        </style>

        {{--
            ================================================================
            RESPONSIVE MOBILE SIDEBAR — off-canvas drawer
            ================================================================
            Everything above this point is untouched. This block is kept
            deliberately separate (its own <style> tag, added last so it
            wins on source order for equal-specificity rules) rather than
            edited into the block above, so it's obvious what changed and
            easy to remove/adjust independently later.

            Design notes:
            - Below 992px (Bootstrap's lg breakpoint, matching
              .navbar-expand-lg already used on the top nav) the sidebar
              becomes a fixed off-canvas drawer instead of a static column.
            - It is opened/closed via a dedicated `body.mobile-nav-open`
              class, controlled by its own JS block further down - this is
              intentionally independent of whatever the existing
              #slidesidebar click handler does to #sidebar/#body for the
              desktop "collapse to icon rail" behavior, so the two never
              have to know about each other. Whether or not that handler
              also toggles `.active` on #sidebar at the same time, every
              rule below re-asserts the full/expanded look for both
              `#sidebar` and `#sidebar.active` alike - so the icon-rail
              styling above simply can't show through on mobile.
            - A backdrop dims and blocks the page behind the open drawer
              and closes it on click, same as any standard off-canvas nav.
            ================================================================
        --}}
        <style>
            .navbar-toggler {
                display: none;
                border: 1px solid rgba(255,255,255,0.5);
                border-radius: 8px;
                padding: 6px 10px;
                background: transparent;
            }
            .navbar-toggler .ui.icon {
                color: #fff;
                margin: 0 !important;
                font-size: 16px !important;
            }
            .navbar-toggler:focus {
                outline: none;
                box-shadow: 0 0 0 2px rgba(255,255,255,0.35);
            }

            #sidebar-backdrop {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(10, 6, 36, 0.45);
                z-index: 1090;
                opacity: 0;
                transition: opacity .25s ease;
            }
            body.mobile-nav-open #sidebar-backdrop {
                display: block;
                opacity: 1;
            }
            body.mobile-nav-open {
                overflow: hidden;
            }

            @media (max-width: 991.98px) {
                .navbar-toggler {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                }

                /* Sidebar becomes a fixed off-canvas drawer, hidden by
                   default and slid in via body.mobile-nav-open. */
                #sidebar {
                    position: fixed !important;
                    top: 0;
                    left: 0;
                    height: 100vh;
                    width: 280px;
                    max-width: 82vw;
                    z-index: 1100;
                    transform: translateX(-100%);
                    transition: transform .3s ease;
                    overflow-y: auto;
                }

                body.mobile-nav-open #sidebar {
                    transform: translateX(0);
                    box-shadow: 4px 0 24px rgba(0,0,0,0.18);
                }

                /* Neutralise the desktop icon-rail look at this
                   breakpoint regardless of whether #sidebar also carries
                   .active from the separate desktop toggle. */
                #sidebar .sidebar-header,
                #sidebar.active .sidebar-header {
                    height: 85px;
                    padding: 8px 10px;
                }
                #sidebar .sidebar-logo,
                #sidebar.active .sidebar-logo {
                    width: auto;
                    max-width: 190px;
                    max-height: 65px;
                }
                #sidebar ul.components > li > a,
                #sidebar.active ul.components > li > a,
                #sidebar ul.components > li.sidebar-dropdown > a,
                #sidebar.active ul.components > li.sidebar-dropdown > a {
                    justify-content: flex-start;
                    padding: 11px 14px;
                }
                #sidebar ul.components > li > a p,
                #sidebar.active ul.components > li > a p {
                    display: block !important;
                }
                #sidebar.active .sidebar-dropdown > a .sidebar-caret {
                    display: inline-block !important;
                }
                #sidebar.active ul.sidebar-submenu.show {
                    display: block !important;
                }

                /* Content always takes the full width on mobile - no
                   margin reserved for a sidebar that's now an overlay. */
                #body,
                #body.active {
                    margin-left: 0 !important;
                    width: 100% !important;
                }

                .container-fluid {
                    padding-left: 14px;
                    padding-right: 14px;
                }

                .page-title {
                    font-size: 19px;
                }
                .page-title .float-right {
                    float: none !important;
                    display: inline-block;
                    margin-top: 8px;
                }
            }

            @media (max-width: 480px) {
                #sidebar {
                    width: 260px;
                }
            }
            /* Kill Bootstrap's auto-generated caret so only our own
   chevron icon renders — having class="dropdown-toggle" was
   triggering Bootstrap's built-in ::after arrow *in addition*
   to the manual <i class="sidebar-caret"> icon, which is what
   was causing the doubled/overlapping arrows. */
#sidebar .sidebar-dropdown > a.dropdown-toggle::after {
    display: none !important;
    content: none !important;
    border: none !important;
    margin: 0 !important;
}

/* Defensive: make the label truncate cleanly instead of ever
   being able to visually collide with the caret again,
   regardless of label length or sidebar width. */
#sidebar ul.components > li > a p {
    flex: 1 1 auto;
    min-width: 0;
    white-space: nowrap;
  
    text-overflow: ellipsis;
}
        </style>
    </head>
    <body>

        <div class="wrapper">

        <nav id="sidebar" class="active" style="background: #ffffff; border-right: 1px solid var(--borderColor);">
           
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
                    <a href="{{ url('dashboard') }}">
                        <i class="ui icon sliders horizontal"></i>
                        <p>{{ __('Dashboard') }}</p>
                    </a>
                </li>
 <li class="sidebar-dropdown">
                    <a href="#quickAccessSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <i class="ui icon linkify"></i>
                        <p>{{ __('Access Management') }}</p>
                    </a>
                    <ul class="collapse list-unstyled sidebar-submenu" id="quickAccessSubmenu">
                        
                        <li>
                            <a href="{{ url('employees/new') }}">
                                <i class="ui icon user plus"></i>
                                <p>{{ __('New Employee') }}</p>
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('fields/company') }}">
                                <i class="ui icon university"></i>
                                <p>{{ __('Company') }}</p>
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('fields/department') }}">
                                <i class="ui icon cubes"></i>
                                <p>{{ __('Department') }}</p>
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('fields/jobtitle') }}">
                                <i class="ui icon pencil alternate"></i>
                                <p>{{ __('Job Title') }}</p>
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('fields/leavetype') }}">
                                <i class="ui icon calendar alternate outline"></i>
                                <p>{{ __('Leave Type') }}</p>
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('clock') }}" target="_blank" rel="noopener noreferrer">
                                <i class="ui icon clock outline"></i>
                                <p>{{ __('Clock In/Out') }}</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="">
                    <a href="{{ url('employees') }}">
                        <i class="ui icon users"></i>
                        <p>{{ __('Employees') }}</p>
                    </a>
                </li>

                <li class="">
                    <a href="{{ url('attendance') }}">
                        <i class="ui icon clock outline"></i>
                        <p>{{ __('Attendances') }}</p>
                    </a>
                </li>
             </li>
                {{--
                    Rota & Schedules submenu - consolidates what were
                    previously 5 separate top-level sidebar links
                    (Schedules, Staff Rota, Today Shifts, plus the new
                    Weekly and Monthly dashboards) into one collapsible
                    group, the same pattern already used above for
                    "Access Management". Keeping all 5 as flat top-level
                    items would have made the sidebar noticeably longer
                    for one feature area; this groups them the way the
                    app already groups the "quick access" shortcuts.
                --}}
                <li class="sidebar-dropdown">
                    <a href="#rotaSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <i class="ui icon calendar alternate outline"></i>
                        <p>{{ __('Rota & Schedules') }}</p>
                    </a>
                    <ul class="collapse list-unstyled sidebar-submenu" id="rotaSubmenu">
                        <!-- <li>
                            <a href="{{ url('schedules') }}">
                                <i class="ui icon list"></i>
                                <p>{{ __('Schedules') }}</p>
                            </a>
                        </li> -->
                        <li>
                            <a href="{{ url('staff-rota') }}">
                                <i class="ui icon clipboard list"></i>
                                <p>{{ __('Staff Rota') }}</p>
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('today-shifts') }}">
                                <i class="ui icon clock outline"></i>
                                <p>{{ __('Today Shifts') }}</p>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('rota.weekly.dashboard') }}">
                                <i class="ui icon table"></i>
                                <p>{{ __('Weekly Dashboard') }}</p>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('monthly.rota') }}">
                                <i class="ui icon calendar outline"></i>
                                <p>{{ __('Monthly Dashboard') }}</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="">
                    <a href="{{ url('leaves') }}">
                        <i class="ui icon calendar plus outline"></i>
                        <p>{{ __('Leave') }}</p>
                    </a>
                </li>
                <li class="">
                    <a href="{{ url('reports') }}">
                        <i class="ui icon chart bar outline"></i>
                        <p>{{ __('Reports') }}</p>
                    </a>
                </li>
                  <li class="sidebar-dropdown">
                    <a href="#contact" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <i class="ui icon calendar alternate outline"></i>
                        <p>{{ __('Contact Manager') }}</p>
                    </a>
                    <ul class="collapse list-unstyled sidebar-submenu" id="contact">
                        <!-- <li>
                            <a href="{{ url('schedules') }}">
                                <i class="ui icon list"></i>
                                <p>{{ __('Schedules') }}</p>
                            </a>
                        </li> -->
                      
                         <li>
               <a href="{{ url('meetings') }}">
                   <i class="ui icon video"></i>
                   <p>{{ __('Meetings') }}</p>
               </a>
             </li>
             <li>
                 <a href="{{ route('emails.index') }}">
                     <i class="ui icon mail"></i>
                     <p>{{ __('Emails') }}</p>
                 </a>
             </li>
              <li>
                    <a href="{{ route('chat') }}" class="chat-menu-link1">
                        <div class="chat-icon-wrapper1">
                            <i class="ui comments icon"></i>
                            <span class="chat-notification-badge chat-badge1 chat-unread-badge" style="display:none;"> </span>
                        </div>
                        <span class="chat-text1">{{ __('Chat') }}</span>
                    </a>
                </li>
                    </ul>
                </li>
            
                <li>
                    <a href="{{ url('payroll') }}">
                        <i class="ui icon money bill alternate outline"></i>
                        <p>{{ __('Payroll') }}</p>
                    </a>
                </li>
               
                  <li class="sidebar-dropdown">
                    <a href="#settings" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <i class="ui icon calendar alternate outline"></i>
                        <p>{{ __('Settings') }}</p>
                    </a>
                    <ul class="collapse list-unstyled sidebar-submenu" id="settings">
                        <!-- <li>
                            <a href="{{ url('schedules') }}">
                                <i class="ui icon list"></i>
                                <p>{{ __('Schedules') }}</p>
                            </a>
                        </li> -->
                        <li>
                    <a href="{{ url('users') }}">
                        <i class="ui icon user circle outline"></i>
                        <p>{{ __('Users') }}</p>
                    </a>
                         </li>
                        <li>
                            <a href="{{ route('audit.index') }}">
                                <i class="ui icon clipboard list"></i>
                                <p>{{ __('Audit Logs') }}</p>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('audit.sessions') }}">
                                <i class="ui icon clock outline"></i>
                                <p>{{ __('Audit Sessions') }}</p>
                            </a>
                        </li>
                       <li>
                    <a href="{{ url('settings') }}">
                        <i class="ui icon cog"></i>
                        <p>{{ __('System Settings') }}</p>
                    </a>
                </li>
                       
                    </ul>
                </li>

                
            </ul>
        </nav>

        <style>
            .chat-menu-link1 {
                display:flex !important;
                align-items:center !important;
                gap:13px;
                width:100%;
                text-decoration:none;
                padding:11px 14px;
                border-radius:12px;
                font-weight:500;
                font-size:14px;
                transition: background 0.3s ease, transform 0.3s ease;
            }

            .chat-menu-link1:hover {
                background: rgba(96,117,112,0.09);
                transform: translateX(3px);
            }

            .chat-icon-wrapper1 {
                position:relative;
                display:flex;
                align-items:center;
                justify-content:center;
                width:32px;
                height:32px;
                min-width:32px;
                border-radius:9px;
                background: rgba(96,117,112,0.08);
                transition: background 0.3s ease, transform 0.3s ease;
            }

            .chat-menu-link1:hover .chat-icon-wrapper1 {
                background: var(--secondaryColor);
                transform: scale(1.06);
            }

            .chat-icon-wrapper1 i {
                font-size:15px;
                margin:0 !important;
                color: var(--secondaryColor) !important;
                transition: color 0.3s ease;
            }

            .chat-menu-link1:hover .chat-icon-wrapper1 i {
                color:#fff !important;
            }

            .chat-text1 {
                display:inline-block;
                line-height:1.2;
                color:#55606b;
            }

            #sidebar.active .chat-text1 {
                display:none;
            }

            #sidebar.active .chat-menu-link1 {
                justify-content:center;
                padding:11px 8px;
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
            <nav class="navbar navbar-expand-lg navbar-light" style="border-bottom: 1px solid var(--borderColor); position: relative; z-index: 1050;">
                <div class="container-fluid">

                    <button type="button" id="slidesidebar" class="ui icon button btn-light-outline" style="color: #fff !important; box-shadow: 0 0 0 1px rgba(255,255,255,0.5) inset !important;">
                        <i class="ui icon bars" style="color: #fff;"></i> <span class="toggle-sidebar-menu" style="color: #fff;">{{ __('Menu') }}</span>
                    </button>

                    {{--
                        Navbar toggler for the top-right nav (Chat /
                        Language / Quick Access / User menu). Bootstrap's
                        .navbar-collapse is hidden below the lg breakpoint
                        unless something adds the .show class to it -
                        previously nothing did, so this whole menu was
                        unreachable on tablet/mobile. Bootstrap's own JS
                        (already loaded) wires this up automatically via
                        the data-toggle/data-target attributes.
                    --}}
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                        <i class="ui icon ellipsis vertical"></i>
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
                                    <div class="menu" tabindex="-1" style="background: #fff; border: 1px solid var(--borderColor);">
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
                                    <div class="menu" tabindex="-1" style="background: #fff; border: 1px solid var(--borderColor);">
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
                                    <div class="menu" tabindex="-1" style="background: #fff; border: 1px solid var(--borderColor);">
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

    {{--
        Sidebar submenu collapse - keeps each toggle link's aria-expanded
        attribute in sync (needed for the caret rotation and highlight
        CSS) since this markup uses Bootstrap's collapse plugin rather
        than Semantic UI here.

        GENERALIZED: previously this only bound show/hide handlers for
        the one hardcoded #quickAccessSubmenu id, so adding a second
        collapsible submenu (Rota & Schedules) would have needed another
        near-duplicate block, and any *future* submenu would silently
        miss this sync entirely unless someone remembered to copy it
        again. Now it wires up every `.sidebar-submenu.collapse` element
        found on the page by matching each one back to the toggle link
        that points at its id, so any submenu added later gets the same
        behavior automatically.
    --}}
    <script>
    $(document).ready(function () {
        $('ul.sidebar-submenu.collapse').each(function () {
            var $submenu = $(this);
            var $toggle = $('a[href="#' + $submenu.attr('id') + '"]');

            $submenu
                .on('show.bs.collapse', function () {
                    $toggle.attr('aria-expanded', 'true');
                })
                .on('hide.bs.collapse', function () {
                    $toggle.attr('aria-expanded', 'false');
                });
        });
    });
    </script>

    {{--
        RESPONSIVE MOBILE SIDEBAR — drawer open/close behavior.

        Deliberately independent of whatever #slidesidebar already does
        for the desktop icon-rail collapse (that logic lives in
        script.js, untouched here). This just layers a second, unrelated
        behavior on the same button: below the lg breakpoint, clicking it
        toggles `body.mobile-nav-open` instead, which the CSS above uses
        to slide #sidebar in/out as an overlay with a backdrop. If the
        existing handler also toggles #sidebar/#body .active at the same
        time, that's harmless - the mobile media query above forces the
        expanded look regardless of .active at this width.
    --}}
    <script>
    $(document).ready(function () {
        if ($('#sidebar-backdrop').length === 0) {
            $('<div id="sidebar-backdrop"></div>').appendTo('body');
        }

        function isMobileWidth() {
            return window.innerWidth < 992;
        }

        function closeMobileDrawer() {
            $('body').removeClass('mobile-nav-open');
        }

        function toggleMobileDrawer() {
            $('body').toggleClass('mobile-nav-open');
        }

        $('#slidesidebar').on('click', function () {
            if (isMobileWidth()) {
                toggleMobileDrawer();
            }
        });

        $(document).on('click', '#sidebar-backdrop', function () {
            closeMobileDrawer();
        });

        // Close the drawer after navigating - but not for the submenu
        // toggle links themselves, which only expand/collapse in place.
        $(document).on('click', '#sidebar ul.components > li > a:not(.dropdown-toggle)', function () {
            if (isMobileWidth()) {
                closeMobileDrawer();
            }
        });

        $(document).on('keyup', function (e) {
            if (e.key === 'Escape') {
                closeMobileDrawer();
            }
        });

        $(window).on('resize', function () {
            if (!isMobileWidth()) {
                closeMobileDrawer();
            }
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