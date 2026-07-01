<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMS UI Preview</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            background: #ececec;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 40px 15px;
        }

        .mobile-container {
            width: 100%;
            max-width: 307;
        }

        .sms-card {
            background: #dcdcdc;
            border-radius: 18px;
            padding: 18px 16px;
            margin-bottom: 20px;
            color: #111;
            line-height: 1.35;
            font-size: 18px;
            font-weight: 500;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
        }

        .sms-card a {
            color: #3b5cff;
            text-decoration: underline;
            word-break: break-all;
        }

        .space-top {
            margin-top: 8px;
        }

        @media (max-width: 480px) {
            .sms-card {
                font-size: 16px;
            }
        }
    </style>
</head>

<body>

    <div class="mobile-container">

        <!-- Payment SMS -->
        <div class="sms-card">
            Thank you for your payment of <br>
            ₹1100.00 to Sikkim Power-Urban.<br>

            Consumer No:
            <a href="#">9898990084</a> <br>
            Txn ID: ABC85853058<br>
            Date: 10-04-2026 | 02:49 PM<br>
            Bharat-Connect Transaction | <br> 
            ConnectPe.
        </div>

        <!-- Complaint SMS -->
        <div class="sms-card">
            Your Complaint has been <br> 
            registered successfully <br> 
            for Bharat-Connect Transaction <br> 
            Ref ID ABC85853058.<br>
            You can track Complaint by <br>
            Complaint ID. Team ConnectPe.
        </div>

    </div>

</body>

</html>