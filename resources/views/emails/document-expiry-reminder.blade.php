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
  .email-content { width: 100%; max-width: 560px; margin: 0 auto; }

  .card {
    background-color: #ffffff;
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid #e5e7eb;
  }

  /* Header colour is set inline per status (green/amber/red) so it also
     works in clients that ignore <style>; these classes are a fallback. */
  .header { padding: 18px 24px; }
  .header-brand { color: #ffffff; font-size: 16px; font-weight: 700; letter-spacing: 0.2px; }
  .header-tag { color: rgba(255,255,255,0.85); font-size: 11.5px; font-weight: 400; margin-top: 2px; }

  .status-row { padding: 20px 24px 0 24px; }
  .status-badge {
    display: inline-block;
    font-size: 11.5px;
    font-weight: 700;
    letter-spacing: 0.3px;
    text-transform: uppercase;
    padding: 5px 12px;
    border-radius: 999px;
  }

  .body-padding { padding: 20px 24px 8px 24px; }
  .greeting { font-size: 15px; color: #111827; margin: 0 0 14px; font-weight: 600; }
  .body-text { font-size: 14px; color: #374151; line-height: 1.7; margin: 0 0 16px; }
  .body-text strong { color: #111827; }

  .detail-table {
    width: 100%;
    border-collapse: collapse;
    background-color: #f9fafb;
    border: 1px solid #eef0f2;
    border-radius: 8px;
    margin: 4px 0 20px;
  }
  .detail-row td { padding: 10px 14px; font-size: 13px; color: #374151; border-bottom: 1px solid #eef0f2; }
  .detail-row:last-child td { border-bottom: none; }
  .detail-label { color: #6b7280; }
  .detail-value { color: #111827; font-weight: 600; text-align: right; }

  .divider { border-top: 1px solid #e5e7eb; margin: 8px 0 20px; }

  .btn-td { border-radius: 6px; }
  .btn-link {
    display: inline-block;
    padding: 12px 28px;
    font-size: 14px;
    font-weight: 600;
    color: #ffffff !important;
    text-decoration: none;
    border-radius: 6px;
  }

  .disclaimer { font-size: 12.5px; color: #9ca3af; line-height: 1.6; margin: 0; }

  .footer-padding { padding: 20px 24px 28px 24px; }
  .footer-text { font-size: 11.5px; color: #9ca3af; line-height: 1.7; text-align: center; }
  .footer-link { color: #6b7280; text-decoration: underline; }

  .spacer-outer { padding: 24px 0; }

  /* Mobile */
  @media only screen and (max-width: 600px) {
    .email-content { width: 100% !important; }
    .card { border-radius: 0 !important; }
    .header, .body-padding, .footer-padding, .status-row { padding-left: 18px !important; padding-right: 18px !important; }
    .btn-link { display: block !important; text-align: center; }
    .detail-value { text-align: right; }
    .spacer-outer { padding: 12px 0 !important; }
  }
</style>
</head>
<body class="bg-outer" style="margin:0;padding:0;background:#f3f4f6;font-family:Arial, Helvetica, sans-serif;">

@php
    // Status logic: expired / expiring soon (<=30 days) / upcoming.
    // Colours mirror the traffic-light badges used across the HRM system.
    if ($daysRemaining < 0) {
        $statusColor = '#dc2626';   // red
        $statusBg    = '#fee2e2';
        $statusText  = 'Expired ' . abs($daysRemaining) . ' day' . (abs($daysRemaining) == 1 ? '' : 's') . ' ago';
    } elseif ($daysRemaining <= 30) {
        $statusColor = '#d97706';   // amber
        $statusBg    = '#fef3c7';
        $statusText  = $daysRemaining . ' day' . ($daysRemaining == 1 ? '' : 's') . ' remaining';
    } else {
        $statusColor = '#16a34a';   // green
        $statusBg    = '#dcfce7';
        $statusText  = $daysRemaining . ' days remaining';
    }
@endphp

<!-- Preheader (hidden preview text shown in inbox list) -->
<div style="display:none;font-size:1px;color:#f3f4f6;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;">
    {{ $preheader ?? $documentLabel.' expires on '.$expiryDate.' — action required' }}
</div>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="email-wrapper">
<tr>
<td align="center" class="spacer-outer">

    <table role="presentation" width="560" cellpadding="0" cellspacing="0" class="email-content">
    <tr>
    <td>

        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="card">

            <!-- Header -->
            <tr>
                <td class="header" style="background-color:{{ $statusColor }};">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td align="left" valign="middle">
                            @if(!empty($logoUrl))
                                <img src="{{ $logoUrl }}" alt="{{ $appName }}" width="32" height="32" style="border-radius:6px;vertical-align:middle;margin-right:10px;display:inline-block;">
                            @endif
                            <span class="header-brand">{{ $appName }}</span>
                            <div class="header-tag">Document Expiry Reminder</div>
                        </td>
                    </tr>
                    </table>
                </td>
            </tr>

            <!-- Status badge -->
            <tr>
                <td class="status-row">
                    <span class="status-badge" style="color:{{ $statusColor }};background-color:{{ $statusBg }};">
                        {{ $statusText }}
                    </span>
                </td>
            </tr>

            <!-- Body -->
            <tr>
                <td class="body-padding">
                    <p class="greeting">Hi {{ $employeeName }},</p>

                    <p class="body-text">
                        This is a reminder that your <strong>{{ $documentLabel }}</strong>
                        @if(!empty($documentNumber))
                            (No. {{ $documentNumber }})
                        @endif
                        is
                        @if($daysRemaining < 0)
                            already <strong>past its expiry date</strong>.
                        @else
                            due to expire on <strong>{{ $expiryDate }}</strong>.
                        @endif
                    </p>

                    <table role="presentation" class="detail-table" cellpadding="0" cellspacing="0">
                        <tr class="detail-row">
                            <td class="detail-label">Document</td>
                            <td class="detail-value">{{ $documentLabel }}</td>
                        </tr>
                        @if(!empty($documentNumber))
                        <tr class="detail-row">
                            <td class="detail-label">Reference No.</td>
                            <td class="detail-value">{{ $documentNumber }}</td>
                        </tr>
                        @endif
                        <tr class="detail-row">
                            <td class="detail-label">Expiry Date</td>
                            <td class="detail-value">{{ $expiryDate }}</td>
                        </tr>
                        <tr class="detail-row">
                            <td class="detail-label">Status</td>
                            <td class="detail-value" style="color:{{ $statusColor }};">{{ $statusText }}</td>
                        </tr>
                    </table>

                    <p class="body-text">
                        Please arrange renewal as soon as possible and share the updated document with HR to avoid any disruption to your employment.
                    </p>

                    @if(!empty($ctaText) && !empty($ctaUrl))
                    <table role="presentation" cellpadding="0" cellspacing="0" style="margin:4px 0 22px;">
                        <tr>
                            <td class="btn-td" style="background-color:{{ $statusColor }};">
                                <a href="{{ $ctaUrl }}" class="btn-link" target="_blank" rel="noopener">{{ $ctaText }}</a>
                            </td>
                        </tr>
                    </table>
                    @endif

                    <div class="divider"></div>

                    <p class="disclaimer">This is an automated reminder from {{ $appName }} HR System.</p>
                </td>
            </tr>

            <!-- Footer -->
            <tr>
                <td class="footer-padding">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="padding-top:14px;border-top:1px solid #f3f4f6;">
                                <p class="footer-text">
                                    Please do not reply directly to this email.<br>
                                    @if(!empty($companyAddress))
                                        {{ $companyAddress }}<br>
                                    @endif
                                    <a href="{{ $preferencesUrl ?? '#' }}" class="footer-link">Notification settings</a>
                                    &nbsp;&middot;&nbsp;
                                    <a href="{{ $supportUrl ?? '#' }}" class="footer-link">Contact HR</a>
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