@extends('layouts.clock')

@section('content')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=JetBrains+Mono:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<meta http-equiv="refresh" content="180">
<style>
    :root {
        --ink: #161B24;
        --panel: #1E2635;
        --panel-light: #2A3345;
        --brass: #C9A54A;
        --brass-light: #E4C878;
        --brass-dim: #8A6F2E;
        --paper: #F6F4EE;
        --paper-dim: #E9E5D8;
        --ink-soft: #8B93A3;
        --ink-soft-2: #5B6472;
        --success: #3FA671;
        --success-dim: #E4F3EA;
        --error: #D0483B;
        --error-dim: #FBEAE7;
        --font-display: 'Space Grotesk', sans-serif;
        --font-mono: 'JetBrains Mono', monospace;
        --font-body: 'Inter', sans-serif;
    }

    * { box-sizing: border-box; }

    .kiosk-stage {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 32px 16px;
        background: radial-gradient(circle at 50% 0%, #232C3D 0%, var(--ink) 60%);
        font-family: var(--font-body);
    }

    .kiosk-card {
        width: 100%;
        max-width: 420px;
        background: var(--panel);
        border-radius: 20px;
        border: 1px solid #313C50;
        overflow: hidden;
    }

    .kiosk-header {
        padding: 18px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid var(--brass-dim);
        background: linear-gradient(180deg, #232C3D 0%, #1E2635 100%);
    }

    .kiosk-brand {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .kiosk-brand-icon {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        border: 2px solid var(--brass);
        position: relative;
        flex-shrink: 0;
    }
    .kiosk-brand-icon::before {
        content: "";
        position: absolute;
        top: 50%; left: 50%;
        width: 2px; height: 8px;
        background: var(--brass);
        transform-origin: bottom center;
        transform: translate(-50%, -100%) rotate(35deg);
    }
    .kiosk-brand-icon::after {
        content: "";
        position: absolute;
        top: 50%; left: 50%;
        width: 2px; height: 6px;
        background: var(--brass);
        transform-origin: bottom center;
        transform: translate(-50%, -100%) rotate(-70deg);
    }

    .kiosk-brand-text {
        font-family: var(--font-display);
        font-size: 13px;
        font-weight: 600;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--paper-dim);
    }

    .kiosk-status-dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        background: var(--success);
        box-shadow: 0 0 0 3px rgba(63,166,113,0.18);
    }

    .lcd-panel {
        margin: 20px 24px 4px;
        background: #10151D;
        border-radius: 12px;
        border: 1px solid #2C3648;
        padding: 18px 20px;
        text-align: center;
        box-shadow: inset 0 2px 6px rgba(0,0,0,0.4);
    }

    .clock-day {
        font-family: var(--font-display);
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: var(--brass-light);
        display: block;
        margin-bottom: 6px;
    }

    .clock-time {
        font-family: var(--font-mono);
        font-size: 42px;
        font-weight: 600;
        color: #EFEAD9;
        letter-spacing: 0.02em;
        line-height: 1;
        display: block;
        text-shadow: 0 0 14px rgba(201,165,74,0.25);
    }

    #show_date {
        font-family: var(--font-mono);
        font-size: 12px;
        color: var(--ink-soft);
        display: block;
        margin-top: 8px;
    }

    .clockinout {
        margin: 20px 24px 0;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .btnclock {
        font-family: var(--font-display);
        font-size: 13px;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        padding: 14px 10px;
        border-radius: 10px;
        border: 1px solid #313C50;
        background: var(--panel-light);
        color: var(--ink-soft);
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .btnclock:hover { border-color: var(--brass-dim); }

    .btnclock.active {
        background: var(--brass);
        border-color: var(--brass-light);
        color: #241C08;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.35), 0 2px 8px rgba(201,165,74,0.25);
    }

    /* Input-method tabs: Scan QR / Enter ID */
    .method-tabs {
        margin: 18px 24px 0;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 6px;
        background: #10151D;
        border: 1px solid #2C3648;
        border-radius: 10px;
        padding: 4px;
    }

    .method-tab {
        font-family: var(--font-display);
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        padding: 10px 8px;
        border-radius: 7px;
        border: none;
        background: transparent;
        color: var(--ink-soft);
        cursor: pointer;
        transition: all 0.15s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .method-tab:hover { color: var(--brass-light); }

    .method-tab.active {
        background: var(--panel-light);
        color: var(--brass-light);
        box-shadow: inset 0 0 0 1px var(--brass-dim);
    }

    .method-panel { display: none; }
    .method-panel.active { display: block; }

    .scanner-wrap {
        margin: 22px 24px 0;
        position: relative;
        border-radius: 14px;
        background: #10151D;
        border: 1px solid #2C3648;
        padding: 16px;
    }

    .scanner-frame {
        position: relative;
        width: 100%;
        max-width: 240px;
        margin: 0 auto;
        border-radius: 8px;
        overflow: hidden;
    }

    #reader { width: 100% !important; }

    .scan-corner {
        position: absolute;
        width: 22px; height: 22px;
        border-color: var(--brass);
        z-index: 5;
        pointer-events: none;
    }
    .scan-corner.tl { top: -1px; left: -1px; border-top: 3px solid; border-left: 3px solid; border-radius: 6px 0 0 0; }
    .scan-corner.tr { top: -1px; right: -1px; border-top: 3px solid; border-right: 3px solid; border-radius: 0 6px 0 0; }
    .scan-corner.bl { bottom: -1px; left: -1px; border-bottom: 3px solid; border-left: 3px solid; border-radius: 0 0 0 6px; }
    .scan-corner.br { bottom: -1px; right: -1px; border-bottom: 3px solid; border-right: 3px solid; border-radius: 0 0 6px 0; }

    .scan-caption {
        text-align: center;
        font-size: 11px;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--ink-soft);
        margin-top: 12px;
        font-family: var(--font-display);
        font-weight: 500;
    }

    /* Manual entry panel */
    .manual-wrap {
        margin: 22px 24px 0;
        border-radius: 14px;
        background: #10151D;
        border: 1px solid #2C3648;
        padding: 20px 18px;
    }

    .manual-label {
        display: block;
        text-align: center;
        font-family: var(--font-display);
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--brass-light);
        margin-bottom: 12px;
    }

    .manual-input {
        width: 100%;
        background: var(--panel-light);
        border: 1px solid #313C50;
        border-radius: 8px;
        color: var(--paper);
        font-family: var(--font-mono);
        font-size: 18px;
        letter-spacing: 0.08em;
        text-align: center;
        text-transform: uppercase;
        padding: 14px 12px;
    }
    .manual-input:focus { outline: none; border-color: var(--brass); }
    .manual-input::placeholder { color: var(--ink-soft-2); text-transform: none; letter-spacing: normal; }

    .manual-submit {
        width: 100%;
        margin-top: 12px;
        font-family: var(--font-display);
        font-size: 13px;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        padding: 13px 10px;
        border-radius: 8px;
        border: 1px solid var(--brass-light);
        background: var(--brass);
        color: #241C08;
        cursor: pointer;
        transition: all 0.15s ease;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.35);
    }
    .manual-submit:hover { background: var(--brass-light); }
    .manual-submit:disabled { opacity: 0.6; cursor: default; }

    .comment { margin-top: 14px; }
    .comment textarea {
        width: 100%;
        background: var(--panel-light);
        border: 1px solid #313C50;
        border-radius: 8px;
        color: var(--paper);
        font-family: var(--font-body);
        font-size: 13px;
        padding: 10px 12px;
        resize: none;
    }
    .comment textarea:focus { outline: none; border-color: var(--brass); }
    .comment textarea::placeholder { color: var(--ink-soft-2); }

    #switch-camera {
        display: block;
        margin: 14px auto 0;
        font-family: var(--font-display);
        font-size: 12px;
        font-weight: 500;
        letter-spacing: 0.04em;
        color: var(--ink-soft);
        background: transparent;
        border: 1px solid #313C50;
        border-radius: 8px;
        padding: 8px 16px;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    #switch-camera:hover { border-color: var(--brass-dim); color: var(--brass-light); }

    .gps-status {
        margin: 14px 24px 0;
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 500;
        text-align: center;
        border: 1px solid transparent;
        font-family: var(--font-body);
    }
    .gps-status.gps-pending { background: rgba(201,165,74,0.12); color: var(--brass-light); border-color: rgba(201,165,74,0.3); }
    .gps-status.gps-ok { background: var(--success-dim); color: #1F5C3C; }
    .gps-status.gps-error { background: var(--error-dim); color: #8C2E24; }

    .message-after {
        margin: 20px 24px 24px;
        background: var(--paper);
        border-radius: 12px;
        padding: 18px 20px;
        display: none;
        position: relative;
        border-left: 4px solid var(--ink-soft-2);
    }
    .message-after.ok { border-left-color: var(--success); }
    .message-after.notok { border-left-color: var(--error); }

    .message-after::before {
        content: "";
        position: absolute;
        top: -6px; left: 16px; right: 16px;
        height: 6px;
        background-image: radial-gradient(circle, var(--ink) 3px, transparent 3px);
        background-size: 12px 12px;
        background-repeat: repeat-x;
        background-position: center;
    }

    .message-after p { margin: 0; font-family: var(--font-body); }

    #greetings, #fullname {
        font-family: var(--font-display);
        font-weight: 600;
        font-size: 15px;
        color: #232B1F;
    }

    #messagewrap { margin-top: 6px; font-size: 13px; color: #4A4536; }
    #type { font-weight: 600; margin-right: 4px; }
    #clockstatus { color: var(--success); font-weight: 600; }
    .message-after.notok #message { color: var(--error); font-weight: 500; }

    .preloader-overlay {
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: rgba(22, 27, 36, 0.92);
        backdrop-filter: blur(6px);
        display: none;
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }
    .preloader-content { text-align: center; }
    .bull-animation { width: 220px; height: auto; animation: bounce 2s infinite; }
    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }

    @media (prefers-reduced-motion: reduce) {
        .bull-animation { animation: none; }
        .btnclock, #switch-camera, .method-tab, .manual-submit { transition: none; }
    }

    @media (max-width: 420px) {
        .clock-time { font-size: 34px; }
        .kiosk-card { border-radius: 14px; }
    }
</style>

<div class="container-fluid">
    <div class="kiosk-stage">
        <div class="kiosk-card">

            <div class="kiosk-header">
                <div class="kiosk-brand">
                    <span class="kiosk-brand-icon" aria-hidden="true"></span>
                    <span class="kiosk-brand-text">{{ __('Time Clock') }}</span>
                </div>
                <span class="kiosk-status-dot" title="{{ __('Online') }}"></span>
            </div>

            <div class="lcd-panel">
                <span id="show_day" class="clock-day"></span>
                <span id="show_time" class="clock-time"></span>
                <span id="show_date"></span>
            </div>

            <div class="clockinout">
                <button class="btnclock timein active" data-type="timein">{{ __("Time In") }}</button>
                <button class="btnclock timeout" data-type="timeout">{{ __("Time Out") }}</button>
            </div>

            <div class="method-tabs">
                <button type="button" class="method-tab active" data-method="scan">{{ __('Scan QR') }}</button>
                <button type="button" class="method-tab" data-method="manual">{{ __('Enter ID') }}</button>
            </div>

            <div class="method-panel active" id="panel-scan">
                <div class="scanner-wrap">
                    <div class="scanner-frame">
                        <span class="scan-corner tl"></span>
                        <span class="scan-corner tr"></span>
                        <span class="scan-corner bl"></span>
                        <span class="scan-corner br"></span>
                        <div id="reader"></div>
                    </div>
                    <p class="scan-caption">{{ __('Scan your QR badge') }}</p>

                    @isset($cc)
                        @if($cc == "on")
                            <div class="inline field comment">
                                <textarea name="comment" class="uppercase lightblue" rows="1" placeholder="{{ __('Enter comment') }}"></textarea>
                            </div>
                        @endif
                    @endisset

                    <button id="switch-camera" type="button">{{ __('Switch camera') }}</button>
                </div>
            </div>

            <div class="method-panel" id="panel-manual">
                <div class="manual-wrap">
                    <label class="manual-label" for="manual-idno">{{ __('Enter your Employee ID') }}</label>
                    <input type="text" id="manual-idno" class="manual-input" placeholder="{{ __('e.g. 735643') }}" autocomplete="off">

                    @isset($cc)
                        @if($cc == "on")
                            <div class="inline field comment">
                                <textarea name="comment-manual" class="uppercase lightblue" rows="1" placeholder="{{ __('Enter comment') }}"></textarea>
                            </div>
                        @endif
                    @endisset

                    <button type="button" id="manual-submit" class="manual-submit">{{ __('Submit') }}</button>
                </div>
            </div>

            @isset($gps)
                @if($gps == "on")
                    <div id="gps-status" class="gps-status gps-pending">
                        <span id="gps-status-text">{{ __("Requesting location access...") }}</span>
                    </div>
                @endif
            @endisset

            <input type="hidden" id="_url" value="{{ url('/') }}">
            <input type="hidden" id="_gps_enabled" value="{{ isset($gps) && $gps == 'on' ? '1' : '0' }}">

            <div class="message-after">
                <p>
                    <span id="greetings">{{ __("Welcome!") }}</span>
                    <span id="fullname"></span>
                </p>
                <p id="messagewrap">
                    <span id="type"></span>
                    <span id="message"></span>
                    <span id="time"></span>
                </p>
            </div>

        </div>
    </div>
</div>

<audio id="sound-in" src="{{ asset('assets/sounds/a1.mp3') }}" preload="auto"></audio>
<audio id="sound-out" src="{{ asset('assets/sounds/a2.mp3') }}" preload="auto"></audio>

<div id="preloader" class="preloader-overlay">
    <div class="preloader-content">
        <img src="{{ asset('assets/images/run.gif') }}" alt="{{ __('Loading') }}" class="bull-animation">
    </div>
</div>

@endsection

@section('scripts')
<script src="https://unpkg.com/html5-qrcode"></script>
<script type="text/javascript">
    var elTime = document.getElementById('show_time');
    var elDate = document.getElementById('show_date');
    var elDay = document.getElementById('show_day');

    var setTime = function() {
        var time = moment().tz(timezone);
        @if($tf == 1)
            elTime.innerHTML = time.format("hh:mm:ss A");
        @else
            elTime.innerHTML = time.format("kk:mm:ss");
        @endif
        elDate.innerHTML = time.format('MMMM D, YYYY');
        elDay.innerHTML = time.format('dddd');
    }
    setTime();
    setInterval(setTime, 1000);

    $('.btnclock').click(function(event) {
        var is_comment = $(this).data("type");
        if (is_comment == "timein") {
            $('.comment').slideDown('200').show();
        } else {
            $('.comment').slideUp('200');
        }
        $('input[name="idno"]').focus();
        $('.btnclock').removeClass('active animated fadeIn');
        $(this).toggleClass('active animated fadeIn');
    });

    function playSound(type) {
        var sound;
        if (type === 'timein') {
            sound = document.getElementById('sound-in');
        } else if (type === 'timeout') {
            sound = document.getElementById('sound-out');
        }
        if (sound) {
            sound.play().catch((error) => {
                console.error("Error playing sound: ", error);
            });
        }
    }

    var gpsEnabled = document.getElementById('_gps_enabled').value === '1';
    var currentLat = null;
    var currentLng = null;
    var gpsError = null;

    function setGpsStatus(message, statusClass) {
        var el = document.getElementById('gps-status');
        var text = document.getElementById('gps-status-text');
        if (!el || !text) return;
        text.textContent = message;
        el.classList.remove('gps-pending', 'gps-ok', 'gps-error');
        el.classList.add(statusClass);
    }

    function initGeolocation() {
        if (!gpsEnabled) return;

        if (!navigator.geolocation) {
            gpsError = "{{ __('Geolocation is not supported by this browser.') }}";
            setGpsStatus(gpsError, 'gps-error');
            return;
        }

        navigator.geolocation.getCurrentPosition(onLocationSuccess, onLocationError, {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 5000
        });

        navigator.geolocation.watchPosition(onLocationSuccess, onLocationError, {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 10000
        });
    }

    function onLocationSuccess(position) {
        currentLat = position.coords.latitude;
        currentLng = position.coords.longitude;
        gpsError = null;
        setGpsStatus("{{ __('Location detected.') }}", 'gps-ok');
    }

    function onLocationError(error) {
        currentLat = null;
        currentLng = null;

        switch (error.code) {
            case error.PERMISSION_DENIED:
                gpsError = "{{ __('Location access denied. Please enable location permissions and reload the page.') }}";
                break;
            case error.POSITION_UNAVAILABLE:
                gpsError = "{{ __('Location information is unavailable.') }}";
                break;
            case error.TIMEOUT:
                gpsError = "{{ __('Location request timed out.') }}";
                break;
            default:
                gpsError = "{{ __('An unknown error occurred while retrieving location.') }}";
                break;
        }
        console.error("Geolocation error:", gpsError);
        setGpsStatus(gpsError, 'gps-error');
    }

    initGeolocation();

    function showPreloader(pauseScanner) {
        document.getElementById('preloader').style.display = 'flex';
        if (pauseScanner && qrScanner && scannerRunning) {
            qrScanner.stop().catch(err => console.error("Error stopping scanner: ", err));
        }
    }

    function hidePreloader() {
        setTimeout(function() {
            document.getElementById('preloader').style.display = 'none';
            // Only resume the camera if the Scan tab is the active method.
            if (currentMethod === 'scan') {
                startScanner();
            }
        }, 5000);
    }

    function showMessage(message, isError) {
        $('.message-after').addClass(isError ? 'notok' : 'ok').removeClass(isError ? 'ok' : 'notok').hide();
        $('#type, #fullname').hide().text("");
        $('#time').hide().text("");
        $('#message').text(message);
        $('.message-after').slideToggle().slideDown('400');
    }

    /**
     * Shared clock-in/out submission, used by BOTH the QR scanner
     * (onScanSuccess) and the manual "Enter ID" tab, so there's exactly
     * one place that talks to /attendance/add.
     */
    function submitClock(idno, fromScanner) {
        let url = $("#_url").val();
        let type = $('.btnclock.active').data("type");
        let comment = fromScanner
            ? $('textarea[name="comment"]').val()
            : $('textarea[name="comment-manual"]').val();

        if (gpsEnabled && (currentLat == null || currentLng == null)) {
            showMessage(gpsError || "{{ __('Waiting for location access. Please allow location permissions and try again.') }}", true);
            return;
        }

        showPreloader(fromScanner);

        $.ajax({
            url: url + '/attendance/add',
            type: 'post',
            dataType: 'json',
            data: {
                idno: idno.toUpperCase(),
                type: type,
                clockin_comment: comment,
                latitude: gpsEnabled ? currentLat : null,
                longitude: gpsEnabled ? currentLng : null
            },
            headers: { 'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content') },
            success: function(response) {
                hidePreloader();

                if (response['error']) {
                    $('.message-after').addClass('notok').removeClass("ok").hide();
                    $('#type, #fullname').hide().text("");
                    $('#time').hide().text("");
                    $('#message').text(response['error']);
                    $('#fullname').text(response['employee']);
                    $('.message-after').slideToggle().slideDown('400');
                } else {
                    let typeText = response['type'] == "timein" ? "{{ __('Time In at') }}" : "{{ __('Time Out at') }}";
                    $('.message-after').addClass('ok').removeClass("notok").hide();
                    $('#type').text(typeText).show();
                    $('#fullname').text(response['firstname'] + ' ' + response['lastname']).show();
                    $('#time').html('<span id=clocktime>' + response['time'] + '</span>.<span id=clockstatus> {{ __("Success!") }}</span>').show();
                    $('#message').text("");

                    if (response['type'] === "timein") {
                        playSound('timein');
                    } else if (response['type'] === "timeout") {
                        playSound('timeout');
                    }

                    $('.message-after').slideToggle().slideDown('400');
                }

                if (!fromScanner) {
                    $('#manual-idno').val('').focus();
                }
            },
            error: function(xhr, status, error) {
                hidePreloader();
                console.error("AJAX request failed:", error);
            }
        });
    }

    function onScanSuccess(decodedText, decodedResult) {
        submitClock(decodedText, true);
    }

    let facingMode = "environment";
    const qrScanner = new Html5Qrcode("reader");
    let scannerRunning = false;

    function startScanner() {
        qrScanner.start(
            { facingMode: facingMode },
            { fps: 10, qrbox: { width: 200, height: 200 } },
            onScanSuccess,
            function onScanError(errorMessage) {}
        ).then(() => {
            scannerRunning = true;
        }).catch(err => {
            console.error("QR Scanner start failed", err);
        });
    }

    function stopScanner() {
        if (scannerRunning) {
            qrScanner.stop().then(() => {
                scannerRunning = false;
            }).catch(err => console.error("Error stopping scanner: ", err));
        }
    }

    startScanner();

    document.getElementById('switch-camera').addEventListener('click', function() {
        facingMode = (facingMode === "environment") ? "user" : "environment";
        stopScanner();
        setTimeout(startScanner, 300);
    });

    /**
     * Scan QR / Enter ID tabs.
     * The camera is stopped while the manual tab is open, both to save
     * power and so a QR code can't get scanned in the background while
     * someone's typing an ID.
     */
    let currentMethod = 'scan';

    $('.method-tab').click(function() {
        let method = $(this).data('method');
        if (method === currentMethod) return;

        currentMethod = method;

        $('.method-tab').removeClass('active');
        $(this).addClass('active');

        $('.method-panel').removeClass('active');
        $('#panel-' + method).addClass('active');

        if (method === 'scan') {
            startScanner();
        } else {
            stopScanner();
            $('#manual-idno').focus();
        }
    });

    $('#manual-submit').click(function() {
        let idno = $('#manual-idno').val().trim();
        if (idno === '') {
            $('#manual-idno').focus();
            return;
        }
        submitClock(idno, false);
    });

    $('#manual-idno').on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            $('#manual-submit').click();
        }
    });

    
    setTimeout(function () {
        location.reload();
    }, 180000); // 3 minutes
</script>
@endsection