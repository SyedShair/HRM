<!DOCTYPE html>
<html>
<head>

    <title>Verify OTP</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:'Poppins',sans-serif;
        }

        body{
            height:100vh;
            display:flex;
            justify-content:center;
            align-items:center;
            background:linear-gradient(135deg,#00a884,#00796b);
        }

        .card{
            width:100%;
            max-width:420px;
            background:#fff;
            padding:40px;
            border-radius:20px;
            box-shadow:0 10px 40px rgba(0,0,0,0.2);
            animation:fadeIn .5s ease;
        }

        @keyframes fadeIn{
            from{
                opacity:0;
                transform:translateY(20px);
            }
            to{
                opacity:1;
                transform:translateY(0);
            }
        }

        h2{
            text-align:center;
            margin-bottom:10px;
            color:#111;
        }

        p{
            text-align:center;
            color:#666;
            margin-bottom:25px;
        }

        input{
            width:100%;
            height:55px;
            border:1px solid #ddd;
            border-radius:12px;
            padding:0 18px;
            font-size:16px;
            outline:none;
            margin-bottom:20px;
        }

        button{
            width:100%;
            height:55px;
            border:none;
            border-radius:12px;
            background:#00a884;
            color:#fff;
            font-size:16px;
            font-weight:600;
            cursor:pointer;
        }

        button:hover{
            background:#008f72;
        }

        .error{
            background:#ffebee;
            color:#c62828;
            padding:12px;
            border-radius:10px;
            margin-bottom:15px;
            font-size:14px;
        }

        .success{
            background:#e8f5e9;
            color:#2e7d32;
            padding:12px;
            border-radius:10px;
            margin-bottom:15px;
            font-size:14px;
        }

    </style>

</head>

<body>

<div class="card">

    <h2>Email OTP Verification</h2>

    <p>Enter the OTP sent to your email</p>

    @if(session('success'))
        <div class="success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="error">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('otp.verify') }}">

        @csrf

        <input type="text"
               name="otp"
               placeholder="Enter 6 Digit OTP"
               required>

        <button type="submit">
            Verify OTP
        </button>

    </form>

</div>

</body>
</html>