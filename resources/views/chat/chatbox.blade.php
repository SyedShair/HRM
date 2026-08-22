@extends(Auth::user()->role_id == 2 ? 'layouts.personal' : 'layouts.default')
@section('styles')

<style>

/* =========================
   GLOBAL
========================= */

html, body {
    height: 100%;
    margin: 0;
}

body {
    overscroll-behavior: none;
}

/* =========================
   WRAPPER
========================= */

.chat-wrapper {
    height: 100vh;
    display: flex;
    overflow: hidden;
}

/* =========================
   SIDEBAR
========================= */

.chat-sidebar{
    height:100vh;
    background:#fff;
    border-right:1px solid #ddd;
    overflow-y:auto;
}

.chat-sidebar-header{
    height:70px;
    background:#ededed;
    display:flex;
    align-items:center;
    padding:0 15px;
    border-bottom:1px solid #ddd;
}

.chat-user{
    padding:15px;
    border-bottom:1px solid #f1f1f1;
    cursor:pointer;
}

.chat-user:hover{
    background:#f5f5f5;
}

.chat-user.active{
    background:#ebebeb;
}

/* =========================
   AVATAR
========================= */

.avatar{
    width:45px;
    height:45px;
    border-radius:50%;
    background:#00a884;
    color:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:bold;
    margin-right:12px;
}

/* =========================
   CHAT AREA
========================= */

.chat-area{
    height:100vh;
    display:flex;
    flex-direction:column;
    overflow:hidden;
}

/* HEADER FIXED */

.chat-header{
    flex:0 0 70px;
    background:#ededed;
    border-bottom:1px solid #ddd;
    display:flex;
    align-items:center;
    padding:0 20px;
}

/* =========================
   MESSAGES (ONLY SCROLL AREA)
========================= */

.chat-messages{
    flex:1;
    overflow-y:auto;
    min-height:0;
    padding:20px;
    background:#efeae2;
    scroll-behavior: smooth;
}

/* MESSAGE BUBBLE */

.message-row{
    display:flex;
    margin-bottom:10px;
}

.message-row.me{
    justify-content:flex-end;
}

.message-bubble{
    max-width:65%;
    padding:10px 14px;
    border-radius:10px;
    font-size:14px;
    line-height:1.4;
}

.message-row.me .message-bubble{
    background:#dcf8c6;
}

.message-row.other .message-bubble{
    background:#fff;
}

/* =========================
   INPUT AREA FIXED
========================= */

.chat-input-area{
    flex:0 0 70px;
    background:#f0f0f0;
    border-top:1px solid #ddd;
    display:flex;
    align-items:center;
    padding:10px;
}

.chat-input{
    flex:1;
    border:none;
    border-radius:30px;
    padding:12px 18px;
    outline:none;
}

.send-btn{
    width:50px;
    height:50px;
    border:none;
    border-radius:50%;
    background:#00a884;
    color:#fff;
    margin-left:10px;
}

/* =========================
   MOBILE
========================= */

@media(max-width:768px){

    .chat-sidebar{
        display:none;
    }

    .chat-wrapper{
        height:100vh;
    }

    .message-bubble{
        max-width:85%;
    }
}
.typing-dots {
    display:inline-block;
}

.typing-dots span {
    width:6px;
    height:6px;
    background:#999;
    display:inline-block;
    border-radius:50%;
    margin-right:3px;
    animation: blink 1.4s infinite both;
}

.typing-dots span:nth-child(2){
    animation-delay:0.2s;
}

.typing-dots span:nth-child(3){
    animation-delay:0.4s;
}

@keyframes blink {
    0%, 80%, 100% { opacity:0; transform:translateY(0); }
    40% { opacity:1; transform:translateY(-3px); }
}
.typing-box{
    padding:5px 20px;
    font-size:13px;
    color:#666;
    display:none;
    background:#efeae2;
}

.typing-dots{
    display:inline-flex;
    align-items:center;
    gap:4px;
}

.typing-dots span{
    width:6px;
    height:6px;
    background:#999;
    border-radius:50%;
    display:inline-block;
    animation: typingBlink 1.4s infinite ease-in-out;
}

.typing-dots span:nth-child(2){
    animation-delay:0.2s;
}

.typing-dots span:nth-child(3){
    animation-delay:0.4s;
}

@keyframes typingBlink {
    0%, 80%, 100% {
        transform: translateY(0);
        opacity: 0.3;
    }
    40% {
        transform: translateY(-4px);
        opacity: 1;
    }
}
.lightbox{
    display:none;
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.9);
    z-index:9999;
    justify-content:center;
    align-items:center;
}

.lightbox img{
    max-width:90%;
    max-height:90%;
    border-radius:10px;
}

.close-btn{
    position:absolute;
    top:20px;
    right:25px;
    font-size:35px;
    color:#fff;
    cursor:pointer;
}
.message-bubble img {
    border-radius: 12px;
    max-width: 220px;
    display: block;
}
.attach-btn {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    border: none;
    background: transparent;
    color: #00a884; /* WhatsApp green */
    font-size: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: 0.2s ease;
}

/* hover effect */
.attach-btn:hover {
    background: rgba(0, 168, 132, 0.1);
}

/* mobile responsiveness */
@media (max-width: 768px) {
    .attach-btn {
        width: 40px;
        height: 40px;
        font-size: 20px;
    }
}.attach-btn {
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}.chat-input {
    flex: 1;
    border: none;
    border-radius: 25px;
    padding: 16px 20px;
    outline: none;
    resize: none;
    font-size: 14px;
    line-height: 20px;
    overflow-y: auto;
    max-height: 180px;
}
</style>

@endsection


@section('content')

<div class="container-fluid p-0">

<div class="row chat-wrapper">

<!-- SIDEBAR -->
<div class="col-md-3 p-0 chat-sidebar">

<div class="chat-sidebar-header">
    <div class="avatar">
        {{ strtoupper(substr(Auth::user()->name,0,1)) }}
    </div>
    <strong>{{ Auth::user()->name }}</strong>
</div>

@foreach($users as $user)

<a href="{{ url('/chat/user/'.$user->id) }}" style="text-decoration:none;color:#000;">

<div class="chat-user {{ $receiver->id == $user->id ? 'active' : '' }}">

<div style="display:flex;align-items:center;">

<div class="avatar">
    {{ strtoupper(substr($user->name,0,1)) }}
</div>

<div>
    <strong>{{ $user->name }}</strong><br>
    <small>{{ $user->role_id == 1 ? 'Manager' : 'Employee' }}</small>
</div>

</div>

</div>

</a>

@endforeach

</div>

<!-- CHAT AREA -->
<div class="col-md-9 p-0">

<div class="chat-area">

<!-- HEADER -->
<div class="chat-header">
    <div class="avatar">
        {{ strtoupper(substr($receiver->name,0,1)) }}
    </div>
    <strong>{{ $receiver->name }}</strong>
</div>

<!-- MESSAGES (ONLY SCROLL AREA) -->
<div class="chat-messages" id="chat-box"></div>
<div id="typing-box" class="typing-box"></div><!-- INPUT -->

<div id="file-preview" style="display:none; padding:8px;"></div>

<div id="image-lightbox" class="lightbox" onclick="closeLightbox()">
    <span class="close-btn">&times;</span>
    <img id="lightbox-img">
</div>
<div class="chat-input-area">
  <input type="file" id="file" style="display:none;" />
    <button  class="attach-btn" type="button" onclick="document.getElementById('file').click()">
&#128206;    </button>
<!--<input type="text" id="message" class="chat-input" placeholder="Type a message">-->
<textarea id="message" class="chat-input" rows="1" placeholder="Type a message...."></textarea>
<button class="send-btn" onclick="sendMessage()">➤</button>

</div>

</div>

</div>

</div>

</div>

@endsection


@section('scripts')

<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function clearFile() {
    $('#file').val('');
    $('#file-preview').hide().html('');
    $('#message').attr('placeholder', 'Type a message....');
}
let receiverId = "{{ $receiver->id }}";
let currentUserId = "{{ auth()->id() }}";
let shouldScroll = true;

// Every message id currently rendered in the DOM, so a duplicate id
// coming back from any poll can never be appended twice - a
// belt-and-suspenders safeguard on top of the backend fix (poll() had
// an operator-precedence bug that made it return the same sent
// message(s) on every single poll; that's fixed server-side now, but
// this also protects against the unrelated edge case of two poll
// requests overlapping under network lag before lastMessageId updates).
let renderedMessageIds = new Set();
let pollInFlight = false;

/* =========================
   SCROLL CHECK
========================= */
function isNearBottom() {
    let box = document.getElementById('chat-box');
    if (!box) return true;

    return (box.scrollHeight - box.scrollTop - box.clientHeight) < 80;
}

/* =========================
   SCROLL TO BOTTOM
========================= */
function scrollToBottom(force = false) {
    let box = document.getElementById('chat-box');

    if (!box) return;

    if (force || shouldScroll) {
        setTimeout(() => {
            box.scrollTop = box.scrollHeight;
        }, 80);
    }
}

/* =========================
   INIT
========================= */
$(document).ready(function () {

    let box = document.getElementById('chat-box');

    if (box) {
        box.addEventListener('scroll', function () {
            shouldScroll = isNearBottom();
        });
    }

    // Initial full load - builds the whole thread once.
    loadMessages(true);

    startPolling();

    // Pause everything while this tab isn't visible - no reason to hit
    // the server for a chat window nobody is looking at. Resume with
    // an immediate poll + a fresh full reconcile the moment it's back.
    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            stopPolling();
        } else {
            loadMessages(true);
            startPolling();
        }
    });

    // Stop timers when navigating away, so a stray interval never
    // outlives the page it was created on.
    window.addEventListener('beforeunload', stopPolling);
});

/* =========================
   POLLING (delta + typing merged into one request)
========================= */
let lastMessageId = 0;
let pollTimer = null;
let fullSyncTimer = null;

function startPolling() {
    if (pollTimer) return; // already running

    // Delta poll: only asks for messages newer than the last one we
    // have, plus typing status, in a single request - replaces the old
    // separate 2s loadMessages() + 1s checkTyping() timers entirely,
    // which were hitting the server twice a second across every open
    // chat window regardless of whether anything had actually changed.
    pollTimer = setInterval(pollForUpdates, 3000);

    // Full reconcile: rebuilds the whole thread from the server every
    // 20s, to pick up edits/deletes that a delta poll can't see (an
    // edited/deleted message has no NEW id, so it's invisible to the
    // "after_id" query). 20s is frequent enough that edits show up
    // promptly without re-fetching full history every few seconds.
    fullSyncTimer = setInterval(function () {
        loadMessages(true);
    }, 20000);
}

function stopPolling() {
    clearInterval(pollTimer);
    clearInterval(fullSyncTimer);
    pollTimer = null;
    fullSyncTimer = null;
}

function pollForUpdates() {

    // Skip this cycle entirely if the previous poll hasn't come back
    // yet, rather than firing an overlapping request on top of it.
    if (pollInFlight) return;
    pollInFlight = true;

    $.ajax({
        url: '/chat/poll/' + receiverId,
        type: 'GET',
        dataType: 'json',
        cache: false,
        data: { after_id: lastMessageId },

        success: function (res) {

            if (res.messages && res.messages.length > 0) {
                appendMessages(res.messages);
            }

            renderTyping(res.typing, res.typing_name);
        },

        error: function () {
            console.log('Poll failed');
        },

        complete: function () {
            pollInFlight = false;
        }
    });
}

/* =========================
   RENDER TYPING
========================= */
function renderTyping(typing, name) {
    if (typing == 1) {
        $('#typing-box').html(`
            <span style="margin-right:8px;">${escapeHtml(name)} is typing</span>
            <div class="typing-dots">
                <span></span><span></span><span></span>
            </div>
        `).show();
    } else {
        $('#typing-box').hide();
    }
}

/* =========================
   LOAD MESSAGES (full thread - initial load + periodic reconcile)
========================= */
function loadMessages(isFullSync) {

    $.ajax({
        url: '/chat/messages/' + receiverId,
        type: 'GET',
        dataType: 'json',
        cache: false,

        success: function (data) {

            renderMessageList(data);

            renderedMessageIds = new Set(data.map(m => m.id));

            if (data.length > 0) {
                lastMessageId = Math.max(...data.map(m => m.id));
            }

            if (isFullSync) {
                scrollToBottom(true);
            } else {
                scrollToBottom();
            }
        },
        error: function () {
            console.log('Failed to load messages');
        }
    });
}

/* =========================
   APPEND (delta poll result - adds only new rows, no full rebuild)
========================= */
function appendMessages(messages) {

    // Drop anything already on screen before building/appending HTML -
    // see the renderedMessageIds comment near the top of this file.
    let newMessages = messages.filter(m => !renderedMessageIds.has(m.id));

    if (newMessages.length === 0) {
        return;
    }

    let html = newMessages.map(buildMessageHtml).join('');

    $('#chat-box').append(html);

    newMessages.forEach(m => renderedMessageIds.add(m.id));
    lastMessageId = Math.max(lastMessageId, ...newMessages.map(m => m.id));

    scrollToBottom();
}

/* =========================
   RENDER (full rebuild - only called on initial load / reconcile)
========================= */
function renderMessageList(data) {
    let html = data.map(buildMessageHtml).join('');
    $('#chat-box').html(html);
}

/* =========================
   BUILD ONE MESSAGE BUBBLE (shared by full render + delta append)
========================= */
function buildMessageHtml(msg) {

    let mine = msg.sender_id == currentUserId;

    let fileHtml = '';

    if (msg.file_url) {

        let ext = msg.file.split('.').pop().toLowerCase();

        if (['jpg','jpeg','png','gif','webp'].includes(ext)) {
            fileHtml = `
                <div style="margin-top:8px;">
                    <img src="${msg.file_url}"
                         onclick="openLightbox('${msg.file_url}')"
                         style="max-width:220px;
                                border-radius:12px;
                                cursor:pointer;
                                display:block;
                                object-fit:cover;">
                </div>
            `;
        } else if (ext === 'pdf') {
            fileHtml = `
                <div style="margin-top:8px;">
                    📄 <a href="${msg.file_url}" target="_blank">View PDF</a>
                </div>
            `;
        } else {
            fileHtml = `
                <div style="margin-top:8px;">
                    ⬇ <a href="${msg.file_url}" target="_blank">Download File</a>
                </div>
            `;
        }
    }

    if (msg.is_deleted == 1) {
        return `
<div class="message-row ${mine ? 'me' : 'other'}">
    <div class="message-bubble deleted">
        This message was deleted
    </div>
</div>`;
    }

    return `
<div class="message-row ${mine ? 'me' : 'other'}">

    <div class="message-bubble">

        <div class="message-text">
            ${escapeHtml(msg.message ?? '')}
        </div>

        ${fileHtml}

        <div style="font-size:11px;color:#777;margin-top:5px;display:flex;justify-content:space-between;align-items:center;">

            <span>${moment(msg.created_at).format('hh:mm A')}</span>

            <div>

                ${mine ? `
                    <span onclick="editMessage(${msg.id}, '${msg.message ? msg.message.replace(/'/g, "\\'") : ''}')"
                          style="cursor:pointer;color:#1976d2;margin-right:10px;">
                        Edit
                    </span>

                    <span onclick="deleteMessage(${msg.id})"
                          style="cursor:pointer;color:#d32f2f;">
                        Delete
                    </span>
                ` : ''}

                ${mine ? `
                    <span style="margin-left:10px;font-size:12px;">
                        ${msg.is_read == 1
                            ? '<span style="color:#1e88e5;">✔✔ Read</span>'
                            : '<span style="color:gray;">✔ Sent</span>'}
                    </span>
                ` : ''}

            </div>

        </div>

    </div>

</div>`;
}

/* =========================
   SEND MESSAGE
========================= */
function sendMessage() {

    let message = $('#message').val().trim();
    let file = $('#file')[0].files[0];

    if (message === '' && !file) return;

    // A file must always be sent with a caption explaining what it's
    // for - never let an attachment go out with no context. The
    // placeholder set in the #file change handler above hints at this;
    // this is the actual enforcement.
    if (file && message === '') {
        toastr.error('Please write what this file is for before sending.');
        $('#message').focus();
        return;
    }

    let formData = new FormData();

    formData.append('_token', '{{ csrf_token() }}');
    formData.append('receiver_id', receiverId);
    formData.append('message', message);

    if (file) {
        formData.append('file', file);
    }

    $.ajax({
        url: '/chat/send',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function (res) {

            $('#message').val('');
            $('#file').val('');

            loadMessages(true);
 clearFile()
            sendTyping(false);
        }
    });
}

/* =========================
   ENTER KEY
========================= */
$(document).on('keypress', '#message', function (e) {
    if (e.which === 13) {
        sendMessage();
    }
});

/* =========================
   EDIT MESSAGE (SWEETALERT)
========================= */
function editMessage(id, oldMessage) {

    Swal.fire({

        title: 'Edit Message',

        input: 'textarea',

        inputValue: oldMessage,

        inputAttributes: {
            autocapitalize: 'off',
            autocomplete: 'off',
            maxlength: 500
        },

        showCancelButton: true,

        confirmButtonText: 'Update',

        cancelButtonText: 'Cancel',

        confirmButtonColor: '#00a884',

        cancelButtonColor: '#d33',

        showLoaderOnConfirm: true,

        didOpen: () => {

            const textarea = Swal.getInput();

            textarea.style.height = "120px";
            textarea.style.resize = "none";
            textarea.style.whiteSpace = "pre-wrap";
            textarea.style.wordBreak = "break-word";
            textarea.style.overflowY = "auto";
        },

        inputValidator: (value) => {

            if (!value || value.trim() === '') {
                return 'Message cannot be empty!';
            }
        },

        preConfirm: (value) => {

            return $.ajax({

                url: '/chat/message/update/' + id,

                type: 'POST',

                data: {
                    _token: '{{ csrf_token() }}',
                    message: value
                }

            }).then(response => {

                if (!response.success) {
                    throw new Error('Update failed');
                }

                return response;

            }).catch(error => {

                Swal.showValidationMessage('Request failed');
            });
        }

    }).then((result) => {

        if (result.isConfirmed) {

            toastr.success('Message updated');

            loadMessages(true);
        }
    });
}/* =========================
   DELETE MESSAGE (SWEETALERT)
========================= */
function deleteMessage(id) {

    Swal.fire({
        title: 'Delete message?',
        text: "This action cannot be undone",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it',
        cancelButtonText: 'Cancel'
    }).then((result) => {

        if (result.isConfirmed) {

            $.post('/chat/message/delete/' + id, {
                _token: '{{ csrf_token() }}'
            }, function (res) {

                if (res.success) {
                    toastr.success('Message deleted');
                    loadMessages(true);
                } else {
                    toastr.error('Delete failed');
                }

            });

        }

    });
}

/* =========================
   ESCAPE HTML
========================= */
function escapeHtml(text) {
    return $('<div>').text(text).html();
}

/* =========================
   TYPING (send-side only - detection now comes back via pollForUpdates)
========================= */
let typingTimer;
let isTyping = false;
$(document).on('input', '#message', function () {

    if (!isTyping) {
        isTyping = true;
        sendTyping(true);
    }

    clearTimeout(typingTimer);

    typingTimer = setTimeout(() => {
        isTyping = false;
        sendTyping(false);
    }, 1000);
});
function sendTyping(status){

    $.ajax({
        url: '/chat/typing',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            receiver_id: receiverId,
            typing: status ? 1 : 0
        }
    });
}

function openLightbox(src){
    document.getElementById('lightbox-img').src = src;
    document.getElementById('image-lightbox').style.display = 'flex';
}

function closeLightbox(){
    document.getElementById('image-lightbox').style.display = 'none';
    document.getElementById('lightbox-img').src = '';
}
/* =========================
   ATTACHMENT TYPE VALIDATION + REQUIRED CAPTION
========================= */
const ALLOWED_FILE_TYPES = {
    'jpg': 'image', 'jpeg': 'image', 'png': 'image', 'gif': 'image', 'webp': 'image',
    'pdf': 'pdf',
    'doc': 'doc', 'docx': 'doc'
};

$('#file').on('change', function () {

    let file = this.files[0];

    if (!file) return;

    let ext = file.name.split('.').pop().toLowerCase();
    let fileType = ALLOWED_FILE_TYPES[ext];

    // Reject anything outside the types this chat actually supports.
    // The file input's `accept` attribute is only a UI hint - most OS
    // file pickers let someone switch to "All Files" and select
    // anything regardless - so this is the real enforcement point on
    // the client, with a clear error instead of silently attaching
    // something that isn't really supported.
    if (!fileType) {
        toastr.error("That file type isn't supported. Please attach an image, PDF, or Word document.");
        clearFile();
        return;
    }

    let reader = new FileReader();
    let preview = $('#file-preview');

    preview.show();

    // Don't guess a caption for the user - prompt them to write their
    // own description of what the file is for via the placeholder, and
    // sendMessage() below refuses to send until they actually do.
    let $message = $('#message');
    if ($message.val().trim() === '') {
        $message.attr('placeholder', 'What is this file for?').focus();
    }

    // IMAGE PREVIEW
    if (fileType === 'image') {

        reader.onload = function (e) {
            preview.html(`
                <div style="position:relative; display:inline-block;">
                    <img src="${e.target.result}"
                         style="max-width:120px;
                                border-radius:10px;">
                    <span onclick="clearFile()"
                          style="position:absolute;top:-8px;right:-8px;
                                 background:red;color:#fff;
                                 border-radius:50%;
                                 width:20px;height:20px;
                                 display:flex;align-items:center;
                                 justify-content:center;
                                 cursor:pointer;">×</span>
                </div>
            `);
        };

        reader.readAsDataURL(file);
    }

    // PDF / DOC PREVIEW
    else {

        preview.html(`
            <div style="background:#eee;padding:8px;border-radius:8px;display:inline-block;">
                📄 ${file.name}
                <span onclick="clearFile()"
                      style="margin-left:10px;color:red;cursor:pointer;">Remove</span>
            </div>
        `);
    }
});

</script>
@endsection