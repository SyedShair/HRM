@if($birthdays->count() > 0)
<div id="birthdayCelebration" class="birthday-overlay">
    <div class="celebration-box">
        <h1 class="title">🎉 Happy Birthday</h1>
        <p class="subtitle">Today we celebrate our team member{{ $birthdays->count() > 1 ? 's' : '' }}</p>

        @foreach($birthdays as $emp)
            <div class="emp-card">
                <img src="{{ asset($emp->avatar ?? 'assets/images/default.png') }}">
                <h2 style="font-size:18px; margin:8px 0 0;">
                    {{ $emp->firstname }} {{ $emp->lastname }}
                </h2>
                <p class="age">
                    Age: {{ \Carbon\Carbon::parse($emp->birthday)->age }}
                </p>
            </div>
        @endforeach

        <button class="close-btn" onclick="closeBirthday()">Close</button>
    </div>
</div>
@endif