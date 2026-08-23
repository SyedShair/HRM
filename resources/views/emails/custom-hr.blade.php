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

  /* Basic styles */
  body, .bg-outer { background-color: #f3f4f6; }
  .email-wrapper { width: 100%; background-color: #f3f4f6; }
  .email-content { width: 100%; max-width: 600px; margin: 0 auto; }

  .card {
    background-color: #ffffff;
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid #e5e7eb;
  }

  .header {
    background-color: #16a34a;
    padding: 22px 32px;
  }
  .header-brand {
    color: #ffffff;
    font-size: 18px;
    font-weight: 700;
    letter-spacing: 0.2px;
  }
  .header-tag {
    color: #d1fae5;
    font-size: 12px;
    font-weight: 400;
    margin-top: 2px;
  }

  .body-padding { padding: 36px 32px 8px 32px; }
  .greeting { font-size: 16px; color: #111827; margin: 0 0 16px; font-weight: 600; }
  .body-text { font-size: 14.5px; color: #374151; line-height: 1.75; white-space: pre-line; }

  .divider { border-top: 1px solid #e5e7eb; margin: 28px 0; }

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

  .signoff { font-size: 13.5px; color: #6b7280; margin: 26px 0 4px; line-height: 1.6; }
  .signoff strong { color: #111827; }

  .footer-padding { padding: 22px 32px 30px 32px; }
  .footer-text { font-size: 11.5px; color: #9ca3af; line-height: 1.7; text-align: center; }
  .footer-link { color: #6b7280; text-decoration: underline; }

  .spacer-outer { padding: 24px 0; }

  /* Mobile */
  @media only screen and (max-width: 600px) {
    .email-content { width: 100% !important; }
    .card { border-radius: 0 !important; }
    .header, .body-padding, .footer-padding { padding-left: 20px !important; padding-right: 20px !important; }
    .body-padding { padding-top: 28px !important; }
    .btn-link { display: block !important; text-align: center; }
    .spacer-outer { padding: 12px 0 !important; }
  }
</style>
</head>
<body class="bg-outer" style="margin:0;padding:0;background:#f3f4f6;font-family:Arial, Helvetica, sans-serif;">

<!-- Preheader (hidden preview text shown in inbox list) -->
<div style="display:none;font-size:1px;color:#f3f4f6;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;">
    {{ $preheader ?? 'A message from '.$appName.' HR' }}
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
                                <img src="{{ $logoUrl }}" alt="{{ $appName }}" width="36" height="36" style="border-radius:6px;vertical-align:middle;margin-right:10px;display:inline-block;">
                            @endif
                            <span class="header-brand">{{ $appName }}</span>
                            <div class="header-tag">Human Resources Notification</div>
                        </td>
                    </tr>
                    </table>
                </td>
            </tr>

            <!-- Body -->
            <tr>
                <td class="body-padding">
                    <p class="greeting">Hi {{ $employeeName }},</p>

                    <div class="body-text">{{ $bodyText }}</div>

                    @if(!empty($ctaText) && !empty($ctaUrl))
                    <table role="presentation" cellpadding="0" cellspacing="0" style="margin:28px 0 8px;">
                        <tr>
                            <td class="btn-td">
                                <a href="{{ $ctaUrl }}" class="btn-link" target="_blank" rel="noopener">{{ $ctaText }}</a>
                            </td>
                        </tr>
                    </table>
                    @endif

                    <div class="divider"></div>

                    <p class="signoff">
                        Regards,<br>
                        <strong>{{ $senderName }}</strong><br>
                        {{ $appName }} HR
                    </p>
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