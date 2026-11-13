<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="x-apple-disable-message-reformatting" />
    <title>Reset Password SiswaMart</title>
    <style type="text/css">
        /* Client-specific Resets */
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
        body { margin: 0; padding: 0; width: 100% !important; background-color: #ffffff; }

        /* Mobile Responsive */
        @media only screen and (max-width: 640px) {
            .email-outer-td {
                padding: 20px 12px !important;
            }
            .email-container {
                width: 100% !important;
                max-width: 100% !important;
            }
            .email-card {
                padding: 28px 20px !important;
                border-radius: 8px !important;
            }
            .email-heading {
                font-size: 20px !important;
                line-height: 1.3 !important;
            }
            .email-btn {
                display: block !important;
                width: 100% !important;
                text-align: center !important;
                box-sizing: border-box !important;
                padding: 14px 20px !important;
            }
            .email-btn-table {
                width: 100% !important;
            }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; width: 100%; background-color: #ffffff; -webkit-font-smoothing: antialiased;">
    <!-- Background Wrapper Table -->
    <table border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#ffffff" style="background-color: #ffffff; margin: 0; padding: 0;">
        <tr>
            <td align="center" class="email-outer-td" style="padding: 40px 20px;">
                <!-- Main Container (max 800px) -->
                <table border="0" cellpadding="0" cellspacing="0" width="100%" class="email-container" style="max-width: 780px; width: 100%; margin: 0 auto; text-align: left;">
                    <tr>
                        <td>
                            <!-- Card Box (#F5F5F7) -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" class="email-card" bgcolor="#F5F5F7" style="background-color: #F5F5F7; border-radius: 12px; padding: 48px; border: 1px solid #ebecee;">
                                <tr>
                                    <td>
                                        <!-- Logo SiswaMart (favicon.png) -->
                                        <table border="0" cellpadding="0" cellspacing="0" style="margin-bottom: 24px;">
                                            <tr>
                                                <td valign="middle">
                                                    <img src="{{ $logoUrl ?? 'https://siswamartid.my.id/favicon.png' }}" alt="SiswaMart" width="60" height="60" style="display: block; width: 60px; height: 60px; border-radius: 12px; border: 0; outline: none; text-decoration: none;" />
                                                </td>
                                            </tr>
                                        </table>

                                        <!-- Title -->
                                        <h1 class="email-heading" style="margin: 0 0 20px 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 24px; font-weight: 800; color: #111827; letter-spacing: -0.02em; line-height: 1.25;">
                                            Reset Password SiswaMart
                                        </h1>

                                        <!-- Body Message -->
                                        <p style="margin: 0 0 16px 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; line-height: 1.6; color: #2d3748;">
                                            Halo{{ !empty($userName ?? $user?->name) ? ' ' . ($userName ?? $user?->name) : '' }},
                                        </p>

                                        <p style="margin: 0 0 28px 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; line-height: 1.6; color: #2d3748;">
                                            Kami menerima permintaan untuk mengatur ulang password akun SiswaMart kamu. Jika kamu mengajukan permintaan ini, klik tombol di bawah untuk melanjutkan proses penggantian password.
                                        </p>

                                        <!-- CTA Button (Navy / Dark Slate #1e2530) -->
                                        <table border="0" cellspacing="0" cellpadding="0" class="email-btn-table" style="margin: 0 0 28px 0;">
                                            <tr>
                                                <td align="left" bgcolor="#1e2530" style="border-radius: 8px;">
                                                    <a href="{{ $url }}" class="email-btn" target="_blank" rel="noopener noreferrer" style="display: inline-block; padding: 14px 34px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; font-weight: 700; color: #ffffff; text-decoration: none; border-radius: 8px; background-color: #1e2530; letter-spacing: 0.01em;">
                                                        Atur Ulang Password
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>

                                        <!-- Expiry Notice -->
                                        <p style="margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13px; line-height: 1.6; color: #6b7280;">
                                            Tautan ini berlaku selama {{ $expire ?? 60 }} menit, sesuai masa berlaku token yang dikonfigurasi pada aplikasi.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Sign-off (Outside the card) -->
                            <div style="margin-top: 32px; padding-left: 4px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; line-height: 1.6; color: #1f2937;">
                                Terima kasih,<br />
                                <strong style="color: #111827; font-weight: 700;">Tim SiswaMart</strong>
                            </div>

                            <!-- Footer Disclaimer (Outside the card) -->
                            <div style="margin-top: 40px; padding: 24px 4px 0 4px; border-top: 1px solid #e5e7eb; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 12px; line-height: 1.6; color: #9ca3af;">
                                Jika kamu tidak meminta pengaturan ulang password, abaikan email ini. Password akunmu tidak akan berubah tanpa tindakan lebih lanjut.
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
