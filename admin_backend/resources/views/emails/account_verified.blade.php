{{-- resources/views/emails/account_verified.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Your SREA Account Has Been Verified</title>
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

        .success-icon {
            text-align: center;
            margin: 20px 0;
        }

        .success-icon span {
            font-size: 64px;
        }

        .badge {
            background: #EAF9EE;
            color: #1E7A3A;
            padding: 8px 16px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 600;
            display: inline-block;
            margin: 16px 0;
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
            <div class="greeting">Congratulations, {{ $user_name }}!</div>

            <div class="success-icon">
                <span>✅</span>
            </div>

            <div style="text-align: center;">
                <div class="badge">Account Verified</div>
            </div>

            <p class="body-text">
                Your SREA account has been successfully verified by the San Rafael MDRRMO.
                You now have full access to all features, including:
            </p>

            <ul class="body-text" style="margin-left: 20px; margin-bottom: 16px;">
                <li>✓ Real-time emergency alerts in your barangay</li>
                <li>✓ Incident reporting with photo uploads</li>
                <li>✓ Access to traffic advisories and announcements</li>
                <li>✓ One‑tap emergency call to MDRRMO</li>
            </ul>

            <p class="body-text">
                You can now log in to the SREA mobile app and start using all services.
            </p>

            <div class="cta-wrap">
                <a href="{{ $app_url }}" class="cta-btn">Open SREA App</a>
            </div>

            <p class="body-text">
                If you have any questions or did not request this verification,
                please contact the San Rafael MDRRMO immediately.
            </p>
        </div>

        <div class="footer">
            <p class="footer-text">
                © {{ date('Y') }} San Rafael MDRRMO — SREA<br />
                Municipal Hall, San Rafael, Bulacan<br />
                <a href="#">Privacy Policy</a> · <a href="#">Help Center</a>
            </p>
            <p class="footer-text" style="margin-top:10px; font-size:11px;">
                This is an automated message. Please do not reply to this email.
            </p>
        </div>
    </div>
</body>

</html>
