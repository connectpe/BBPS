<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification</title>
</head>
<body style="margin:0; padding:0; background:#f4f6f8; font-family: Arial, Helvetica, sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="padding:30px 0;">
        <tr>
            <td align="center">

                <table width="100%" cellpadding="0" cellspacing="0" style="max-width:420px; background:#ffffff; border-radius:14px; box-shadow:0 10px 30px rgba(0,0,0,0.12); overflow:hidden;">

                    <tr>
                        <td style="background:linear-gradient(135deg,#667eea,#764ba2); padding:25px; text-align:center; color:#ffffff;">
                            <h2 style="margin:0; font-size:22px;"> Email Verification</h2>
                            <p style="margin:8px 0 0; font-size:14px; opacity:0.9;">
                                Secure Admin Login
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:30px; text-align:center;">

                            <p style="font-size:16px; color:#333;">
                                Hello <strong>{{ $data['name'] }}</strong> 👋
                            </p>

                            <p style="font-size:14px; color:#666; line-height:1.6;">
                                Use the OTP below to complete your admin login.
                                This OTP is valid for a limited time ⏳
                            </p>

                            <div style="
                                margin:25px auto;
                                display:inline-block;
                                padding:15px 30px;
                                font-size:28px;
                                letter-spacing:6px;
                                font-weight:bold;
                                color:#ffffff;
                                background:linear-gradient(135deg,#43cea2,#185a9d);
                                border-radius:10px;
                                animation:pulse 1.5s infinite;
                            ">
                                {{ $data['otp'] }}
                            </div>

                            <p style="font-size:13px; color:#777; margin-top:20px;">
                                Logged in as <br>
                                <strong>{{ $data['email'] }}</strong>
                            </p>

                           

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background:#f9fafb; padding:20px; text-align:center; font-size:12px; color:#999;">
                            If you didn’t request this login, please ignore this email.
                            <br><br>
                            <strong>Thanks,</strong><br>
                            Rafi Fintech Pvt. Ltd.
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

    <style>
        @keyframes pulse {
            0% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(67,206,162,0.6);
            }
            70% {
                transform: scale(1.05);
                box-shadow: 0 0 0 12px rgba(67,206,162,0);
            }
            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(67,206,162,0);
            }
        }
    </style>

</body>
</html>
