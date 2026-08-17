/**
 * Layout-wide behaviour: Semantic UI dropdown init, flash message
 * notifications, and chat unread-count polling.
 *
 * Reads its dynamic values (flash messages, poll interval) from
 * `window.LayoutConfig`, which is set by a small inline script in the
 * Blade layout using Laravel's Js::from() helper for safe escaping.
 */
$(document).ready(function () {
    // Global Semantic UI dropdown init (top-nav language / quick access / user menu).
    // Pages that need custom dropdown behavior (e.g. an onChange callback)
    // should add the "no-global-init" class to that specific dropdown
    // element; this selector explicitly skips those, so page-level JS
    // registered in @yield('scripts') is free to configure them itself.
    $('.ui.dropdown').not('.no-global-init').dropdown({
        on: 'click',
        action: 'activate'
    });

    var config = window.LayoutConfig || {};

    if (config.successMessage) {
        $.notify(
            { icon: 'ui icon check', message: config.successMessage },
            { type: 'success', timer: 400 }
        );
    }

    if (config.errorMessage) {
        $.notify(
            { icon: 'ui icon times', message: config.errorMessage },
            { type: 'danger', timer: 400 }
        );
    }

    initChatUnreadPolling(config.chatPollIntervalMs || 15000);
});

/**
 * Polls unread chat count on an interval. Pauses while the tab is hidden
 * (Page Visibility API) so backgrounded tabs stop generating load, and
 * fires one immediate refresh when the tab becomes visible again.
 */
function initChatUnreadPolling(intervalMs) {
    var timer = null;

    function loadUnreadMessages() {
        $.ajax({
            url: '/chat/unread-count',
            type: 'GET',
            dataType: 'json',
            cache: false,
            success: function (response) {
                var count = response.count;
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

    function start() {
        if (timer) return;
        loadUnreadMessages();
        timer = setInterval(loadUnreadMessages, intervalMs);
    }

    function stop() {
        clearInterval(timer);
        timer = null;
    }

    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            stop();
        } else {
            start();
        }
    });

    start();
}