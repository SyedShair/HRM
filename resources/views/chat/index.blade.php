@extends(Auth::user()->role_id == 2 ? 'layouts.personal' : 'layouts.default')
@section('styles')

<style>

/* =========================
   PAGE BACKGROUND
========================= */

body {
    background: #f4f6f9;
}

/* =========================
   WRAPPER
========================= */

.chat-users-wrapper {
    padding: 20px;
}

/* =========================
   CARD
========================= */

.chat-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    overflow: hidden;
}

/* HEADER */

.chat-card-header {
    background: #00a884;
    color: #fff;
    padding: 15px;
    font-weight: 600;
    font-size: 16px;
}

/* LIST */

.user-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

/* USER ITEM */

.user-item {
    display: flex;
    align-items: center;
    padding: 15px;
    border-bottom: 1px solid #eee;
    transition: 0.2s;
    text-decoration: none;
    color: #333;
}

.user-item:hover {
    background: #f0f9f7;
}

/* =========================
   AVATAR WRAPPER (IMPORTANT FIX)
========================= */

.avatar-wrapper {
    position: relative;
    width: 45px;
    height: 45px;
    margin-right: 12px;
    flex-shrink: 0;
}

.user-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: #00a884;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

/* UNREAD BADGE */
.avatar-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: red;
    color: #fff;
    font-size: 11px;
    min-width: 18px;
    height: 18px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    border: 2px solid #fff;
}

/* USER INFO */

.user-info {
    display: flex;
    flex-direction: column;
    flex: 1;
}

.user-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.user-name {
    font-weight: 600;
    font-size: 15px;
}

.user-role {
    font-size: 12px;
    color: #777;
}

/* ARROW */

.arrow {
    color: #bbb;
    font-size: 18px;
}

/* MOBILE */

@media(max-width:768px){
    .chat-users-wrapper {
        padding: 10px;
    }
}

</style>

@endsection


@section('content')

<div class="container-fluid chat-users-wrapper">

<div class="row justify-content-center">

<div class="col-md-6">

<div class="card chat-card">

    <!-- HEADER -->
    <div class="chat-card-header">
        💬 Chat Users
    </div>

    <!-- USERS -->
    <ul class="user-list">

        @foreach($users as $user)

        <a href="{{ url('/chat/user/'.$user->id) }}" class="user-item">

            <!-- AVATAR WITH BADGE -->
            <div class="avatar-wrapper">

                <div class="user-avatar">
                    {{ strtoupper(substr($user->name,0,1)) }}
                </div>

                @if($user->unread_count > 0)
                    <div class="avatar-badge">
                        {{ $user->unread_count }}
                    </div>
                @endif

            </div>

            <!-- INFO -->
            <div class="user-info">

                <div class="user-top">

                    <div class="user-name">
                        {{ $user->name }}
                    </div>

                </div>

                <div class="user-role">
                    {{ $user->role_id == 1 ? 'Admin' : 'Employee' }}
                </div>

            </div>

            <!-- ARROW -->
            <div class="arrow">›</div>

        </a>

        @endforeach

    </ul>

</div>

</div>

</div>

</div>

@endsection