@extends('layouts.clock')

@section('content')

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=JetBrains+Mono:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

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

    * {
        box-sizing: border-box;
    }

    .kiosk-stage {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 32px 16px;

        background:
            radial-gradient(
                circle at 50% 0%,
                #232C3D 0%,
                var(--ink) 60%
            );

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

        background:
            linear-gradient(
                180deg,
                #232C3D 0%,
                #1E2635 100%
            );

        gap: 10px;
    }

    .kiosk-brand {
        display: flex;
        align-items: center;

        gap: 12px;

        min-width: 0;
    }

    .kiosk-avatar {
        width: 38px;
        height: 38px;

        border-radius: 50%;

        border: 2px solid var(--brass);

        background: var(--panel-light);

        color: var(--brass-light);

        font-family: var(--font-display);

        font-size: 13px;
        font-weight: 700;

        letter-spacing: 0.02em;

        display: flex;
        align-items: center;
        justify-content: center;

        flex-shrink: 0;
    }

    .kiosk-brand-text-wrap {
        min-width: 0;
    }

    .kiosk-brand-text {
        font-family: var(--font-display);

        font-size: 13px;
        font-weight: 600;

        letter-spacing: 0.06em;

        color: var(--paper-dim);

        display: block;

        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .kiosk-brand-sub {
        font-family: var(--font-body);

        font-size: 11px;

        color: var(--ink-soft);

        display: block;

        margin-top: 2px;
    }

    .kiosk-status-dot {
        width: 8px;
        height: 8px;

        border-radius: 50%;

        background: var(--success);

        box-shadow:
            0 0 0 3px rgba(63, 166, 113, 0.18);

        flex-shrink: 0;
    }

    .lcd-panel {
        margin: 20px 24px 4px;

        background: #10151D;

        border-radius: 12px;

        border: 1px solid #2C3648;

        padding: 18px 20px;

        text-align: center;

        box-shadow:
            inset 0 2px 6px rgba(0,0,0,0.4);
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

        font-size: clamp(28px, 9vw, 42px);

        font-weight: 600;

        color: #EFEAD9;

        letter-spacing: 0.02em;

        line-height: 1;

        display: block;

        text-shadow:
            0 0 14px rgba(201,165,74,0.25);
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

        min-height: 46px;

        border-radius: 10px;

        border: 1px solid #313C50;

        background: var(--panel-light);

        color: var(--ink-soft);

        cursor: pointer;

        transition: all 0.15s ease;
    }

    .btnclock:hover {
        border-color: var(--brass-dim);
    }

    .btnclock.active {
        background: var(--brass);

        border-color: var(--brass-light);

        color: #241C08;

        box-shadow:
            inset 0 1px 0 rgba(255,255,255,0.35),
            0 2px 8px rgba(201,165,74,0.25);
    }

    .action-wrap {
        margin: 22px 24px 0;

        border-radius: 14px;

        background: #10151D;

        border: 1px solid #2C3648;

        padding: 20px 18px;
    }

    .comment {
        display: none;
    }

    .comment textarea {
        width: 100%;

        background: var(--panel-light);

        border: 1px solid #313C50;

        border-radius: 8px;

        color: var(--paper);

        font-family: var(--font-body);

        font-size: 16px;

        padding: 10px 12px;

        resize: none;
    }

    .comment textarea:focus {
        outline: none;

        border-color: var(--brass);
    }

    .comment textarea::placeholder {
        color: var(--ink-soft-2);
    }

    .personal-submit {
        width: 100%;

        min-height: 48px;

        font-family: var(--font-display);

        font-size: 13px;

        font-weight: 600;

        letter-spacing: 0.06em;

        text-transform: uppercase;

        padding: 14px 10px;

        border-radius: 10px;

        border: 1px solid var(--brass-light);

        background: var(--brass);

        color: #241C08;

        cursor: pointer;

        transition: all 0.15s ease;

        box-shadow:
            inset 0 1px 0 rgba(255,255,255,0.35);
    }

    .personal-submit:hover {
        background: var(--brass-light);
    }

    .personal-submit:disabled {
        opacity: 0.6;

        cursor: default;
    }

    .no-employee-notice {
        margin: 16px 24px 0;

        padding: 10px 14px;

        border-radius: 8px;

        font-size: 12px;

        text-align: center;

        background: var(--error-dim);

        color: #8C2E24;

        border: 1px solid rgba(208,72,59,0.3);
    }

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

    .gps-status.gps-pending {
        background: rgba(201,165,74,0.12);

        color: var(--brass-light);

        border-color: rgba(201,165,74,0.3);
    }

    .gps-status.gps-ok {
        background: var(--success-dim);

        color: #1F5C3C;
    }

    .gps-status.gps-error {
        background: var(--error-dim);

        color: #8C2E24;
    }

    .message-after {
        margin: 20px 24px 24px;

        background: var(--paper);

        border-radius: 12px;

        padding: 18px 20px;

        display: none;

        position: relative;

        border-left: 4px solid var(--ink-soft-2);
    }

    .message-after.ok {
        border-left-color: var(--success);
    }

    .message-after.notok {
        border-left-color: var(--error);
    }

    .message-after::before {
        content: "";

        position: absolute;

        top: -6px;
        left: 16px;
        right: 16px;

        height: 6px;

        background-image:
            radial-gradient(
                circle,
                var(--ink) 3px,
                transparent 3px
            );

        background-size: 12px 12px;

        background-repeat: repeat-x;

        background-position: center;
    }

    .message-after p {
        margin: 0;

        font-family: var(--font-body);
    }

    #greetings,
    #fullname {
        font-family: var(--font-display);

        font-weight: 600;

        font-size: 15px;

        color: #232B1F;
    }

    #messagewrap {
        margin-top: 6px;

        font-size: 13px;

        color: #4A4536;
    }

    #type {
        font-weight: 600;

        margin-right: 4px;
    }

    #clockstatus {
        color: var(--success);

        font-weight: 600;
    }

    .message-after.notok #message {
        color: var(--error);

        font-weight: 500;
    }

    .preloader-overlay {
        position: fixed;

        top: 0;
        left: 0;

        width: 100%;
        height: 100%;

        background: rgba(22, 27, 36, 0.92);

        backdrop-filter: blur(6px);

        display: none;

        z-index: 9999;

        align-items: center;
        justify-content: center;
    }

    .preloader-content {
        text-align: center;
    }

    .bull-animation {
        width: 220px;

        height: auto;

        animation: bounce 2s infinite;
    }

    @keyframes bounce {
        0%, 100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-10px);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .bull-animation {
            animation: none;
        }

        .btnclock,
        .personal-submit {
            transition: none;
        }
    }

    @media (max-width: 420px) {
        .kiosk-card {
            border-radius: 14px;
        }

        .kiosk-header {
            padding: 14px 16px;
        }

        .lcd-panel,
        .clockinout,
        .action-wrap,
        .gps-status,
        .message-after {
            margin-left: 16px;
            margin-right: 16px;
        }
    }
</style>

<div class="container-fluid">

    <div class="kiosk-stage">

        <div class="kiosk-card">

            {{-- =====================================================
                 HEADER
            ====================================================== --}}

            <div class="kiosk-header">

                <div class="kiosk-brand">

                    <span class="kiosk-avatar" aria-hidden="true">
                        {{ strtoupper(substr($firstname ?? '', 0, 1)) }}{{ strtoupper(substr($lastname ?? '', 0, 1)) }}
                    </span>

                    <div class="kiosk-brand-text-wrap">

                        <span class="kiosk-brand-text">
                            {{ trim(($firstname ?? '') . ' ' . ($lastname ?? '')) ?: __('My Time Clock') }}
                        </span>

                        <span class="kiosk-brand-sub">
                            {{ __('Personal Clock In / Out') }}
                        </span>

                    </div>

                </div>

                <span
                    class="kiosk-status-dot"
                    title="{{ __('Online') }}"
                ></span>

            </div>


            {{-- =====================================================
                 CLOCK DISPLAY
            ====================================================== --}}

            <div class="lcd-panel">

                <span
                    id="show_day"
                    class="clock-day"
                ></span>

                <span
                    id="show_time"
                    class="clock-time"
                ></span>

                <span
                    id="show_date"
                ></span>

            </div>


            {{-- =====================================================
                 TIME IN / TIME OUT
            ====================================================== --}}

            <div class="clockinout">

                <button
                    type="button"
                    class="btnclock timein active"
                    data-type="timein"
                >
                    {{ __('Time In') }}
                </button>

                <button
                    type="button"
                    class="btnclock timeout"
                    data-type="timeout"
                >
                    {{ __('Time Out') }}
                </button>

            </div>


            {{-- =====================================================
                 ACTION
            ====================================================== --}}

            <div class="action-wrap">

                @if(isset($cc) && $cc === 'on')

                    <div class="inline field comment">

                        <textarea
                            name="comment"
                            rows="2"
                            placeholder="{{ __('Enter comment (optional)') }}"
                        ></textarea>

                    </div>

                @endif


                <button
                    type="button"
                    id="personal-submit"
                    class="personal-submit"
                    @if(isset($cc) && $cc === 'on')
                        style="margin-top:14px;"
                    @endif
                >
                    {{ __('Submit') }}
                </button>

            </div>


            {{-- =====================================================
                 EMPLOYEE ERROR
            ====================================================== --}}

            @if(empty($idno))

                <div class="no-employee-notice">

                    {{ __('Your account is not linked to an employee record. Please contact HR/Admin.') }}

                </div>

            @endif


            {{-- =====================================================
                 GPS
            ====================================================== --}}

            @if(isset($gps) && $gps === 'on')

                <div
                    id="gps-status"
                    class="gps-status gps-pending"
                >

                    <span id="gps-status-text">
                        {{ __('Requesting location access...') }}
                    </span>

                </div>

            @endif


            {{-- =====================================================
                 HIDDEN DATA
            ====================================================== --}}

            <input
                type="hidden"
                id="_url"
                value="{{ url('/') }}/"
            >

            <input
                type="hidden"
                id="_gps_enabled"
                value="{{ isset($gps) && $gps === 'on' ? '1' : '0' }}"
            >

            <input
                type="hidden"
                id="_my_idno"
                value="{{ strtoupper($idno ?? '') }}"
            >


            {{-- =====================================================
                 RESPONSE MESSAGE
            ====================================================== --}}

            <div class="message-after">

                <p>

                    <span id="greetings">
                        {{ __('Welcome!') }}
                    </span>

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


{{-- =============================================================
     SOUNDS
============================================================== --}}

<audio
    id="sound-in"
    src="{{ asset('assets/sounds/a1.mp3') }}"
    preload="auto"
></audio>

<audio
    id="sound-out"
    src="{{ asset('assets/sounds/a2.mp3') }}"
    preload="auto"
></audio>


{{-- =============================================================
     PRELOADER
============================================================== --}}

<div
    id="preloader"
    class="preloader-overlay"
>

    <div class="preloader-content">

        <img
            src="{{ asset('assets/images/run.gif') }}"
            alt="{{ __('Loading') }}"
            class="bull-animation"
        >

    </div>

</div>

@endsection


@section('scripts')

<script type="text/javascript">

(function () {

    "use strict";


    /*
     * ============================================================
     * CONFIGURATION
     * ============================================================
     */

    var orgTimezone = @json($tz ?? 'UTC');

    var timeFormat = @json($tf ?? 1);

    var csrfToken = $('meta[name="csrf-token"]').attr('content');


    /*
     * ============================================================
     * CLOCK ELEMENTS
     * ============================================================
     */

    var elTime = document.getElementById('show_time');

    var elDate = document.getElementById('show_date');

    var elDay = document.getElementById('show_day');


    /*
     * ============================================================
     * LIVE CLOCK
     * ============================================================
     */

    function setTime() {

        if (
            typeof moment === 'undefined' ||
            typeof moment.tz === 'undefined'
        ) {
            console.error('Moment Timezone is not loaded.');
            return;
        }

        var time = moment().tz(orgTimezone);

        if (parseInt(timeFormat) === 1) {

            elTime.innerHTML =
                time.format("hh:mm:ss A");

        } else {

            elTime.innerHTML =
                time.format("HH:mm:ss");
        }

        elDate.innerHTML =
            time.format("MMMM D, YYYY");

        elDay.innerHTML =
            time.format("dddd");
    }


    setTime();

    setInterval(setTime, 1000);


    /*
     * ============================================================
     * TIME IN / TIME OUT BUTTON
     * ============================================================
     */

    $('.btnclock').on('click', function () {

        var isTimein =
            $(this).data("type") === "timein";

        if (isTimein) {

            $('.comment')
                .stop(true, true)
                .slideDown(200)
                .show();

        } else {

            $('.comment')
                .stop(true, true)
                .slideUp(200);
        }

        $('.btnclock')
            .removeClass('active');

        $(this)
            .addClass('active');
    });


    /*
     * ============================================================
     * PLAY SOUND
     * ============================================================
     */

    function playSound(type) {

        var sound = null;

        if (type === 'timein') {

            sound =
                document.getElementById('sound-in');

        } else if (type === 'timeout') {

            sound =
                document.getElementById('sound-out');
        }

        if (!sound) {
            return;
        }

        try {

            sound.currentTime = 0;

            var promise = sound.play();

            if (promise !== undefined) {

                promise.catch(function (error) {

                    console.warn(
                        "Unable to play clock sound:",
                        error
                    );

                });
            }

        } catch (error) {

            console.warn(
                "Unable to play clock sound:",
                error
            );
        }
    }


    /*
     * ============================================================
     * GPS
     * ============================================================
     */

    var gpsElement =
        document.getElementById('_gps_enabled');

    var idnoElement =
        document.getElementById('_my_idno');

    var gpsEnabled =
        gpsElement &&
        gpsElement.value === '1';

    var myIdno =
        idnoElement
            ? idnoElement.value.trim()
            : '';

    var currentLat = null;

    var currentLng = null;

    var gpsError = null;


    /*
     * ============================================================
     * GPS STATUS
     * ============================================================
     */

    function setGpsStatus(
        message,
        statusClass
    ) {

        var el =
            document.getElementById('gps-status');

        var text =
            document.getElementById(
                'gps-status-text'
            );

        if (!el || !text) {
            return;
        }

        text.textContent = message;

        el.classList.remove(
            'gps-pending',
            'gps-ok',
            'gps-error'
        );

        el.classList.add(statusClass);
    }


    /*
     * ============================================================
     * GPS SUCCESS
     * ============================================================
     */

    function onLocationSuccess(position) {

        if (
            !position ||
            !position.coords
        ) {
            return;
        }

        currentLat =
            position.coords.latitude;

        currentLng =
            position.coords.longitude;

        gpsError = null;

        setGpsStatus(
            "{{ __('Location detected.') }}",
            'gps-ok'
        );
    }


    /*
     * ============================================================
     * GPS ERROR
     * ============================================================
     */

    function onLocationError(error) {

        currentLat = null;

        currentLng = null;

        if (!error) {

            gpsError =
                "{{ __('Unable to determine your location.') }}";

        } else {

            switch (error.code) {

                case error.PERMISSION_DENIED:

                    gpsError =
                        "{{ __('Location access denied. Please enable location permissions and reload the page.') }}";

                    break;

                case error.POSITION_UNAVAILABLE:

                    gpsError =
                        "{{ __('Location information is unavailable.') }}";

                    break;

                case error.TIMEOUT:

                    gpsError =
                        "{{ __('Location request timed out.') }}";

                    break;

                default:

                    gpsError =
                        "{{ __('An unknown error occurred while retrieving location.') }}";

                    break;
            }
        }

        console.error(
            "Geolocation error:",
            gpsError
        );

        setGpsStatus(
            gpsError,
            'gps-error'
        );
    }


    /*
     * ============================================================
     * INITIALIZE GPS
     * ============================================================
     */

    function initGeolocation() {

        if (!gpsEnabled) {
            return;
        }

        if (!navigator.geolocation) {

            gpsError =
                "{{ __('Geolocation is not supported by this browser.') }}";

            setGpsStatus(
                gpsError,
                'gps-error'
            );

            return;
        }


        /*
         * Get immediate location.
         */
        navigator.geolocation.getCurrentPosition(
            onLocationSuccess,
            onLocationError,
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 5000
            }
        );


        /*
         * Keep location updated.
         */
        navigator.geolocation.watchPosition(
            onLocationSuccess,
            onLocationError,
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 10000
            }
        );
    }


    initGeolocation();


    /*
     * ============================================================
     * PRELOADER
     * ============================================================
     */

    function showPreloader() {

        var preloader =
            document.getElementById('preloader');

        if (preloader) {

            preloader.style.display = 'flex';
        }
    }


    function hidePreloader() {

        var preloader =
            document.getElementById('preloader');

        if (preloader) {

            preloader.style.display = 'none';
        }
    }


    /*
     * ============================================================
     * SHOW ERROR
     * ============================================================
     */

    function showError(message, employee) {

        $('.message-after')
            .removeClass('ok')
            .addClass('notok')
            .hide();

        $('#type')
            .hide()
            .text('');

        $('#time')
            .hide()
            .text('');

        $('#fullname')
            .hide()
            .text('');

        if (employee) {

            $('#fullname')
                .text(employee)
                .show();
        }

        $('#message')
            .text(message || "{{ __('Something went wrong. Please try again.') }}");

        $('.message-after')
            .stop(true, true)
            .slideDown(400);
    }


    /*
     * ============================================================
     * SHOW SUCCESS
     * ============================================================
     */

    function showSuccess(response) {

        var typeText =
            response.type === "timein"
                ? "{{ __('Time In at') }}"
                : "{{ __('Time Out at') }}";


        /*
         * Full name.
         *
         * IMPORTANT:
         * This fixes the original syntax error:
         *
         * $('#fullname').text(response['firstname']show();
         */
        var fullName =
            (
                response.firstname || ''
            ) +
            ' ' +
            (
                response.lastname || ''
            ) +
            (
                response.mi
                    ? ' ' + response.mi
                    : ''
            );


        fullName =
            fullName
                .replace(/\s+/g, ' ')
                .trim();


        $('.message-after')
            .removeClass('notok')
            .addClass('ok')
            .hide();


        $('#type')
            .text(typeText)
            .show();


        $('#fullname')
            .text(fullName)
            .show();


        /*
         * IMPORTANT:
         * Use a proper ID quote around clocktime.
         */
        $('#time')
            .html(
                '<span id="clocktime">' +
                (response.time || '') +
                '</span>' +
                '<span id="clockstatus">' +
                ' {{ __("Success!") }}' +
                '</span>'
            )
            .show();


        $('#message')
            .text('');


        /*
         * Play appropriate sound.
         */
        if (response.type === 'timein') {

            playSound('timein');

        } else if (response.type === 'timeout') {

            playSound('timeout');
        }


        $('.message-after')
            .stop(true, true)
            .slideDown(400);


        /*
         * Clear comment after successful request.
         */
        $('textarea[name="comment"]')
            .val('');
    }


    /*
     * ============================================================
     * SUBMIT CLOCK
     * ============================================================
     */

    function submitClock() {

        /*
         * No employee linked.
         */
        if (!myIdno) {

            showError(
                "{{ __('Your account is not linked to an employee record. Please contact HR/Admin.') }}"
            );

            return;
        }


        /*
         * GPS is required when enabled.
         */
        if (
            gpsEnabled &&
            (
                currentLat === null ||
                currentLng === null
            )
        ) {

            showError(
                gpsError ||
                "{{ __('Waiting for location access. Please allow location permissions and try again.') }}"
            );

            return;
        }


        /*
         * Determine selected action.
         */
        var activeButton =
            $('.btnclock.active');

        var type =
            activeButton.data('type');


        if (
            type !== 'timein' &&
            type !== 'timeout'
        ) {

            showError(
                "{{ __('Please select Time In or Time Out.') }}"
            );

            return;
        }


        /*
         * Comment.
         */
        var comment =
            $('textarea[name="comment"]')
                .val() || '';


        /*
         * Button.
         */
        var $btn =
            $('#personal-submit');


        /*
         * Prevent double click.
         */
        if ($btn.prop('disabled')) {
            return;
        }


        $btn.prop(
            'disabled',
            true
        );


        showPreloader();


        /*
         * URL.
         */
        var url =
            $("#_url").val();


        /*
         * Ensure URL ends with slash.
         */
        if (url.slice(-1) !== '/') {
            url += '/';
        }


        /*
         * Submit.
         */
        $.ajax({

            url:
                url + 'personal/attendance/add',

            type:
                'POST',

            dataType:
                'json',

            data: {

                idno:
                    myIdno,

                type:
                    type,

                clockin_comment:
                    comment,

                latitude:
                    gpsEnabled
                        ? currentLat
                        : null,

                longitude:
                    gpsEnabled
                        ? currentLng
                        : null
            },

            headers: {

                'X-CSRF-TOKEN':
                    csrfToken
            },


            /*
             * SUCCESS
             */
            success: function(response) {

                hidePreloader();

                $btn.prop(
                    'disabled',
                    false
                );


                if (
                    response &&
                    response.error
                ) {

                    showError(
                        response.error,
                        response.employee || ''
                    );

                    return;
                }


                if (
                    !response ||
                    !response.type
                ) {

                    showError(
                        "{{ __('Invalid server response.') }}"
                    );

                    return;
                }


                showSuccess(response);
            },


            /*
             * ERROR
             */
            error: function(xhr) {

                hidePreloader();

                $btn.prop(
                    'disabled',
                    false
                );


                console.error(
                    "AJAX request failed:",
                    xhr
                );


                /*
                 * Try to show Laravel's JSON error.
                 */
                var message =
                    "{{ __('Something went wrong. Please try again.') }}";


                if (
                    xhr.responseJSON &&
                    xhr.responseJSON.error
                ) {

                    message =
                        xhr.responseJSON.error;

                } else if (
                    xhr.responseJSON &&
                    xhr.responseJSON.message
                ) {

                    message =
                        xhr.responseJSON.message;
                }


                showError(message);
            }
        });
    }


    /*
     * ============================================================
     * SUBMIT BUTTON
     * ============================================================
     */

    $('#personal-submit').on(
        'click',
        function () {

            submitClock();
        }
    );


    /*
     * ============================================================
     * ENTER KEY SUPPORT FOR COMMENT
     * ============================================================
     *
     * Ctrl + Enter submits the clock.
     */
    $('textarea[name="comment"]').on(
        'keydown',
        function (event) {

            if (
                event.ctrlKey &&
                event.key === 'Enter'
            ) {

                event.preventDefault();

                submitClock();
            }
        }
    );

})();

</script>

@endsection