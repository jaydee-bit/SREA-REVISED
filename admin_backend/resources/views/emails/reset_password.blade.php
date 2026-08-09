{{-- resources/views/emails/reset_password.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reset Your SREA Password</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: #F8F9FF;
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 15px;
            color: #0D0F1C;
            -webkit-font-smoothing: antialiased;
        }

        .wrapper {
            max-width: 560px;
            margin: 40px auto;
            padding: 0 16px 40px;
        }

        .header {
            background: linear-gradient(135deg, #1A3ADB 0%, #2B4EFF 100%);
            border-radius: 16px 16px 0 0;
            padding: 36px 32px 28px;
            text-align: center;
        }

        .logo-text {
            display: inline-block;
            font-size: 48px;
            font-weight: 900;
            font-style: italic;
            letter-spacing: 4px;
            line-height: 1;
        }

        .logo-sr {
            color: #FFFFFF;
        }

        .logo-ea {
            color: #FF3B30;
        }

        .header-tagline {
            color: #BBC4FF;
            font-size: 12px;
            letter-spacing: 1px;
            margin-top: 6px;
            text-transform: uppercase;
        }

        .header-divider {
            width: 48px;
            height: 2px;
            background: rgba(255, 255, 255, 0.2);
            margin: 16px auto 0;
            border-radius: 2px;
        }

        .card {
            background: #FFFFFF;
            border-radius: 0 0 16px 16px;
            padding: 36px 32px 32px;
            border: 1px solid #E4E7F0;
            border-top: none;
        }

        .greeting {
            font-size: 20px;
            font-weight: 700;
            color: #0D0F1C;
            margin-bottom: 12px;
        }

        .body-text {
            font-size: 15px;
            color: #5C6080;
            line-height: 1.7;
            margin-bottom: 16px;
        }

        .cta-wrap {
            text-align: center;
            margin: 32px 0;
        }

        .cta-btn {
            display: inline-block;
            background: linear-gradient(135deg, #1A3ADB 0%, #2B4EFF 100%);
            color: #FFFFFF !important;
            text-decoration: none;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 0.5px;
            padding: 16px 40px;
            border-radius: 12px;
        }

        .expiry-box {
            background: #FFF0EA;
            border: 1px solid rgba(255, 107, 43, 0.3);
            border-radius: 8px;
            padding: 12px 16px;
            margin: 20px 0;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .expiry-icon {
            font-size: 16px;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .expiry-text {
            font-size: 13px;
            color: #FF6B2B;
            line-height: 1.5;
        }

        .backup-section {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #E4E7F0;
        }

        .backup-label {
            font-size: 12px;
            color: #AAADBB;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .backup-link {
            font-size: 12px;
            color: #2B4EFF;
            word-break: break-all;
            line-height: 1.6;
        }

        .security-box {
            background: #F0F2FB;
            border-radius: 8px;
            padding: 14px 16px;
            margin-top: 20px;
        }

        .security-text {
            font-size: 12px;
            color: #5C6080;
            line-height: 1.6;
        }

        .security-text strong {
            color: #0D0F1C;
        }

        .footer {
            text-align: center;
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px solid #E4E7F0;
        }

        .footer-text {
            font-size: 12px;
            color: #AAADBB;
            line-height: 1.7;
        }

        .footer-text a {
            color: #2B4EFF;
            text-decoration: none;
        }

        @media (max-width: 480px) {
            .card {
                padding: 24px 20px;
            }

            .header {
                padding: 28px 20px 22px;
            }

            .logo-text {
                font-size: 36px;
            }
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="header">
            <div class="logo-text">
                <span class="logo-sr">SR</span><span class="logo-ea">EA</span>
            </div>
            <div class="header-tagline">San Rafael Emergency Alert</div>
            <div class="header-divider"></div>
        </div>

        <div class="card">
            <div class="greeting">Password Reset Request</div>
            <p class="body-text">
                Hi <strong>{{ $user_name }}</strong>,
            </p>
            <p class="body-text">
                We received a request to reset the password for your SREA account
                associated with <strong>{{ $user_email }}</strong>.
                If you made this request, click the button below to set a new password.
            </p>

            <div class="cta-wrap">
                <a href="{{ $reset_link }}" class="cta-btn">Reset Password</a>
            </div>

            <div class="expiry-box">
                <span class="expiry-icon">⏱</span>
                <span class="expiry-text">
                    <strong>This link expires in 30 minutes.</strong>
                    After that, you'll need to request a new reset link.
                </span>
            </div>

            <p class="body-text">
                If you did not request a password reset, you can safely ignore this email.
                Your password will remain unchanged and no action is required.
            </p>

            <div class="security-box">
                <p class="security-text">
                    🔒 <strong>Security note:</strong> SREA will never ask for your
                    password via email, SMS, or phone call. This link can only be used
                    once and will expire after use. If you believe your account has been
                    compromised, contact your barangay admin or the San Rafael MDRRMO.
                </p>
            </div>

            <div class="backup-section">
                <div class="backup-label">If the button doesn't work, copy this link:</div>
                <div class="backup-link">{{ $reset_link }}</div>
            </div>
        </div>

        <div class="footer">
            <p class="footer-text">
                © {{ date('Y') }} San Rafael MDRRMO — SREA<br />
                Municipal Hall, San Rafael, Bulacan<br />
                <a href="#">Privacy Policy</a> · <a href="#">Help Center</a>
            </p>
            <p class="footer-text" style="margin-top:10px; font-size:11px;">
                You're receiving this because a password reset was requested for this email address.
            </p>
        </div>
    </div>
</body>

</html>
