<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="color-scheme" content="light">
<meta name="supported-color-schemes" content="light">
<title>{{ $appName }}</title>
<!--[if mso]>
<noscript>
<xml>
<o:OfficeDocumentSettings>
<o:PixelsPerInch>96</o:PixelsPerInch>
</o:OfficeDocumentSettings>
</xml>
</noscript>
<style>
    table, td, div, h1, p { font-family: Arial, Helvetica, sans-serif; }
</style>
<![endif]-->
<style>
  /* Client resets */
  body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
  table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
  img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; display: block; }
  body { margin: 0; padding: 0; width: 100% !important; height: 100% !important; }

  body, .bg-outer { background-color: #f3f4f6; }
  .email-wrapper { width: 100%; background-color: #f3f4f6; }
  .email-content { width: 100%; max-width: 600px; margin: 0 auto; }

  .card {
    background-color: #ffffff;
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid #e5e7eb;
  }

  .header { background-color: #16a34a; padding: 18px 24px; }
  .header-brand { color: #ffffff; font-size: 16px; font-weight: 700; letter-spacing: 0.2px; }
  .header-tag { color: #d1fae5; font-size: 11.5px; font-weight: 400; margin-top: 2px; }

  .body-padding { padding: 26px 24px 8px 24px; }
  .greeting { font-size: 15px; color: #111827; margin: 0 0 14px; font-weight: 600; }
  .body-text { font-size: 14px; color: #374151; line-height: 1.7; margin: 0 0 18px; }
  .body-text strong { color: #111827; }

  /* Summary info table */
  .summary-table { width: 100%; border-collapse: collapse; margin: 0 0 20px; }
  .summary-row td { padding: 6px 0; }
  .summary-label { color: #6b7280; font-size: 12px; width: 150px; }
  .summary-value { font-weight: 700; color: #111827; font-size: 13.5px; }

  /* Schedule table */
  .schedule-table { width: 100%; border-collapse: collapse; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; }
  .schedule-head th {
    background-color: #16a34a;
    color: #ffffff;
    text-align: left;
    font-size: 11px;
    letter-spacing: 0.3px;
    text-transform: uppercase;
    padding: 10px 10px;
  }
  .schedule-row td {
    padding: 10px;
    font-size: 12.5px;
    color: #111827;
    border-bottom: 1px solid #eef0f2;
    font-weight: 600;
  }
  .schedule-row:last-child td { border-bottom: none; }
  .day-off { color: #dc2626 !important; }
  .badge {
    display: inline-block;
    font-size: 10.5px;
    font-weight: 700;
    padding: 3px 9px;
    border-radius: 999px;
  }
  .badge-working { color: #16a34a; background-color: #dcfce7; }
  .badge-off { color: #dc2626; background-color: #fee2e2; }

  .btn-td { border-radius: 6px; background-color: #16a34a; }
  .btn-link {
    display: inline-block;
    padding: 12px 28px;
    font-size: 14px;
    font-weight: 600;
    color: #ffffff !important;
    text-decoration: none;
    border-radius: 6px;
  }

  .disclaimer { font-size: 12.5px; color: #9ca3af; line-height: 1.6; margin: 22px 0 0; }
  .divider { border-top: 1px solid #e5e7eb; margin: 22px 0 0; }

  .footer-padding { padding: 20px 24px 28px 24px; }
  .footer-text { font-size: 11.5px; color: #9ca3af; line-height: 1.7; text-align: center; }
  .footer-link { color: #6b7280; text-decoration: underline; }

  .spacer-outer { padding: 24px 0; }

  /* Mobile */
  @media only screen and (max-width: 600px) {
    .email-content { width: 100% !important; }
    .card { border-radius: 0 !important; }
    .header, .body-padding, .footer-padding { padding-left: 18px !important; padding-right: 18px !important; }
    .schedule-head th { font-size: 10px; padding: 8px 6px; }
    .schedule-row td { font-size: 11.5px; padding: 8px 6px; }
    .summary-label { width: 120px; font-size: 11px; }
    .summary-value { font-size: 12.5px; }
    .btn-link { display: block !important; text-align: center; }
    .spacer-outer { padding: 12px 0 !important; }
  }
</style>
</head>
<body class="bg-outer" style="margin:0;padding:0;background:#f3f4f6;font-family:Arial, Helvetica, sans-serif;">

<!-- Preheader (hidden preview text shown in inbox list) -->
<div style="display:none;font-size:1px;color:#f3f4f6;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;">
    {{ $preheader ?? 'Your schedule for '.$datefrom.' to '.$dateto.' is ready.' }}
</div>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="email-wrapper">
<tr>
<td align="center" class="spacer-outer">

    <table role="presentation" width="600" cellpadding="0" cellspacing="0" class="email-content">
    <tr>
    <td>

        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="card">

            <!-- Header -->
            <tr>
                <td class="header">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td align="left" valign="middle">
                            @if(!empty($logoUrl))
                                <img src="{{ $logoUrl }}" alt="{{ $appName }}" width="32" height="32" style="border-radius:6px;vertical-align:middle;margin-right:10px;display:inline-block;">
                            @endif
                            <span class="header-brand">{{ $appName }}</span>
                            <div class="header-tag">Weekly Schedule Notification</div>
                        </td>
                    </tr>
                    </table>
                </td>
            </tr>

            <!-- Body -->
            <tr>
                <td class="body-padding">
                    <p class="greeting">Hi {{ $employeeName }},</p>

                    <p class="body-text">Your weekly schedule has been set. Here are the details:</p>

                    <!-- Summary -->
                    <table role="presentation" class="summary-table" cellpadding="0" cellspacing="0">
                        <tr class="summary-row">
                            <td class="summary-label">Schedule Period</td>
                            <td class="summary-value">{{ $datefrom }} &ndash; {{ $dateto }}</td>
                        </tr>
                        <tr class="summary-row">
                            <td class="summary-label">Weekly Hours</td>
                            <td class="summary-value">{{ $weeklyHours }} hrs</td>
                        </tr>
                        @if(!empty($scheduledHours))
                        <tr class="summary-row">
                            <td class="summary-label">Scheduled</td>
                            <td class="summary-value">{{ $scheduledHours }} / {{ $weeklyHours }} hrs</td>
                        </tr>
                        @endif
                    </table>

                    <!-- Schedule table -->
                    <table role="presentation" class="schedule-table" cellpadding="0" cellspacing="0">
                        <tr class="schedule-head">
                            <th>Day</th>
                            <th>Status</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                        </tr>
                        @foreach($days as $index => $day)
                            @php
                                $shift = $shifts[$day] ?? null;
                                $off = $shift ? (bool) $shift->is_off : false;
                                $timeIn = $shift && $shift->time_in ? substr($shift->time_in, 0, 5) : null;
                                $timeOut = $shift && $shift->time_out ? substr($shift->time_out, 0, 5) : null;
                                $rowBg = $index % 2 === 1 ? '#f9fafb' : '#ffffff';
                            @endphp
                            <tr class="schedule-row" style="background-color:{{ $rowBg }};">
                                <td>{{ $day }}</td>
                                <td>
                                    <span class="badge {{ $off ? 'badge-off' : 'badge-working' }}">
                                        {{ $off ? 'Day Off' : 'Working' }}
                                    </span>
                                </td>
                                <td>{{ $off ? '—' : ($timeIn ?: '—') }}</td>
                                <td>{{ $off ? '—' : ($timeOut ?: '—') }}</td>
                            </tr>
                        @endforeach
                    </table>

                    @if(!empty($ctaText) && !empty($ctaUrl))
                    <table role="presentation" cellpadding="0" cellspacing="0" style="margin:24px 0 4px;">
                        <tr>
                            <td class="btn-td">
                                <a href="{{ $ctaUrl }}" class="btn-link" target="_blank" rel="noopener">{{ $ctaText }}</a>
                            </td>
                        </tr>
                    </table>
                    @endif

                    <div class="divider"></div>

                    <p class="disclaimer">If you have any questions about this schedule, please contact your manager.</p>
                </td>
            </tr>

            <!-- Footer -->
            <tr>
                <td class="footer-padding">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="padding-top:14px;border-top:1px solid #f3f4f6;">
                                <p class="footer-text">
                                    This is an automated message from {{ $appName }}. Please do not reply directly to this email.<br>
                                    @if(!empty($companyAddress))
                                        {{ $companyAddress }}<br>
                                    @endif
                                    <a href="{{ $preferencesUrl ?? '#' }}" class="footer-link">Notification settings</a>
                                    &nbsp;&middot;&nbsp;
                                    <a href="{{ $supportUrl ?? '#' }}" class="footer-link">Contact support</a>
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

        </table>
        <!-- /card -->

        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td align="center" style="padding:16px 20px 0;">
                    <p style="font-size:11px;color:#b0b5bd;margin:0;">
                        &copy; {{ date('Y') }} {{ $appName }}. All rights reserved.
                    </p>
                </td>
            </tr>
        </table>

    </td>
    </tr>
    </table>
    <!-- /email-content -->

</td>
</tr>
</table>

</body>
</html>