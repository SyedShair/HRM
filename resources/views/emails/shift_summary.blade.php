<!doctype html>
<html>
<head>
  <meta name="viewport" content="width=device-width">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <title>Shift Summary Email</title>
  <style>
    body {
      background-color: #f6f6f6;
      font-family: Arial, sans-serif;
      font-size: 14px;
      margin: 0;
      padding: 0;
      color: #333;
    }

    .container {
      max-width: 600px;
      margin: 0 auto;
      padding: 20px;
      background-color: #ffffff;
      border-radius: 6px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .header {
      text-align: center;
      padding-bottom: 20px;
    }

    .header img {
      height: 60px;
    }

    h2 {
      color: #333;
      text-align: center;
      font-size: 24px;
      margin-bottom: 20px;
    }

    p {
      line-height: 1.6;
      margin: 10px 0;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin: 20px 0;
    }

    th, td {
      border: 1px solid #ddd;
      padding: 10px;
      text-align: left;
    }

    th {
      background-color: #f7f7f7;
    }

    .footer {
      text-align: center;
      color: #888;
      font-size: 12px;
      margin-top: 20px;
    }

    .footer a {
      color: #888;
      text-decoration: underline;
    }

    @media only screen and (max-width: 620px) {
      .container {
        padding: 10px;
        width: 100% !important;
      }

      h2 {
        font-size: 20px !important;
      }

      p, td, th {
        font-size: 16px !important;
      }

      table {
        font-size: 16px;
      }

      .header img {
        height: 50px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <a href="https://www.jpingos.com/">
        <img src="{{ asset('assets/sounds/logo.png') }}" alt="Jpingos Logo">
      </a>
    </div>

    <h2>Shift Summary Notification</h2>

    <p>Dear Manager,</p>
    <p>This is a summary of {{ $firstname }}'s shift today. Please review the details below:</p>

    <table border="0">
      <tr>
        <th>Detail</th>
        <th>Value</th>
      </tr>
      <tr>
        <td>Employee Name</td>
        <td>{{ $firstname }}</td>
      </tr>
      <tr>
        <td>Employee ID</td>
        <td>{{ $id }}</td>
      </tr>
      <tr>
        <td>Time In</td>
        <td>{{ $timein }}</td>
      </tr>
      <tr>
        <td>Time Out</td>
        <td>{{ $timeout }}</td>
      </tr>
      <tr>
        <td>Total Hours</td>
        <td>{{ $totalhour }}</td>
      </tr>
      <tr>
        <td>Today Pay</td>
        <td>{{ $totalTodayPay }}</td>
      </tr>
    </table>

    <p>If there are any issues with your shift data, please contact your manager or developer immediately.</p>
    <p>Thank you for your hard work today!</p>

    <p style="margin-top: 30px;">Best regards,<br>Management</p>

    <div class="footer">
      <p>Don't forget to add your company address here</p>
      <p><a href="#">Unsubscribe</a> if you no longer wish to receive these emails.</p>
    </div>
  </div>
</body>
</html>
