<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Payment Receipt</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.5;
        }

        .container {
            width: 100%;
            border: 1px solid #e5e5e5;
            padding: 20px;
            margin: 0 auto;
        }

        .header {
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 25px;
            text-transform: uppercase;
            color: #222;
        }

        /* Top Header Table */
        .brand-table {
            width: 100%;
            margin-bottom: 30px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 15px;
        }

        .brand-left {
            width: 50%;
            vertical-align: middle;
        }

        .brand-right {
            width: 50%;
            text-align: right;
            vertical-align: middle;
        }

        .brand-logo {
            font-size: 22px;
            font-weight: 800;
            color: #0d6efd;
            letter-spacing: 1px;
        }

        .business-info {
            font-size: 11px;
            color: #555;
        }

        .business-name {
            font-size: 14px;
            font-weight: bold;
            color: #000;
            display: block;
            margin-bottom: 2px;
        }

        /* Sections */
        .section-title {
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
            color: #555;
            text-transform: uppercase;
        }

        .row-table {
            width: 100%;
            margin-bottom: 20px;
        }

        .col-cell {
            width: 50%;
            vertical-align: top;
            padding-right: 10px;
        }

        .item {
            margin-bottom: 8px;
        }

        .label {
            color: #777;
            display: inline-block;
            width: 110px;
        }

        .value {
            font-weight: 600;
            color: #333;
        }

        .status {
            color: #28a745;
            font-weight: bold;
            text-transform: capitalize;
        }

        /* Amount & Footer */
        .amount-box {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
        }

        .amount-value {
            font-size: 20px;
            font-weight: bold;
            color: #0d6efd;
        }

        .footer-note {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="header">Payment Receipt</div>

        <table class="brand-table">
            <tr>
                <td class="brand-left">
                    <div class="brand-logo">CONNECTPE</div>
                </td>
                <td class="brand-right">
                    <div class="business-info">
                        <!-- <span class="business-name">{{ $txn->user->business->business_name ?? 'N/A' }}</span>
                        {{ $txn->user->business->address ?? '-' }} -->

                        <img src="{{ public_path('assets/image/Logo/b-assured-logo.jpg') }}"
                            alt="Logo"
                            style="width: 70px;">


                    </div>
                </td>
            </tr>
        </table>
        
        <table class="row-table">
            <tr>
                <td class="col-cell">
                    <div class="section-title">Transaction Info</div>
                    <div class="item">
                        <span class="label">ConnectPe ID:</span>
                        <span class="value">{{ $txn->connectpe_id ?? '-' }}</span>
                    </div>
                    <div class="item">
                        <span class="label">Reference No:</span>
                        <span class="value">{{ $txn->payment_ref_id ?? '-' }}</span>
                    </div>
                    <div class="item">
                        <span class="label">Status:</span>
                        <span class="status">{{ $txn->status ?? '-' }}</span>
                    </div>
                    <div class="item">
                        <span class="label">Date:</span>
                        <span class="value">{{ optional($txn->created_at)->format('d M Y h:i A') }}</span>
                    </div>
                    <div class="item">
                        <span class="label">Mobile:</span>
                        <span class="value">{{ $txn->mobile_number ?? '-' }}</span>
                    </div>
                    <div class="item">
                        <span class="label">Operator:</span>
                        <span class="value">
                            {{ $txn->operator->name ?? '-' }}
                            {{ !empty($txn->operator->code) ? '[' . $txn->operator->code . ']' : '' }}
                        </span>
                    </div>
                    <div class="item">
                        <span class="label">Circle:</span>
                        <span class="value">
                            {{ $txn->circle->name ?? '-' }}
                            {{ !empty($txn->circle->code) ? '[' . $txn->circle->code . ']' : '' }}
                        </span>
                    </div>
                </td>

                <td class="col-cell">
                    <div class="section-title">Payer Details</div>
                    <div class="item">
                        <span class="label">Customer Name:</span>
                        <span class="value">{{ $txn->user->name ?? '-' }}</span>
                    </div>
                    <div class="item">
                        <span class="label">Email ID:</span>
                        <span class="value">{{ $txn->user->email ?? '-' }}</span>
                    </div>
                    <div class="item">
                        <span class="label">Organization:</span>
                        <span class="value">{{ $txn->user->business->business_name ?? 'N/A' }}</span>
                    </div>
                    <div class="item">
                        <span class="label">Address:</span>
                        <span class="value">{{ $txn->user->business->address ?? '-' }}</span>
                    </div>

                    <div style="margin-top: 25px;">
                        <div class="section-title">Payment Mode</div>
                        <div class="item">
                            <span class="label">Method:</span>
                            <span class="value">{{ strtoupper($txn->transaction_type ?? 'WALLET') }}</span>
                        </div>
                        <div class="item">
                            <span class="label">Remark:</span>
                            <span class="value">Recharge Success</span>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <table class="row-table">
            <tr>
                <td style="width: 50%;">
                    <div class="amount-box">
                        <div style="font-size: 11px; color: #555; margin-bottom: 2px;">Total Amount Paid</div>
                        <div class="amount-value">₹ {{ number_format($txn->amount ?? 0, 2) }}</div>
                    </div>
                </td>
                <td style="width: 50%; vertical-align: bottom; text-align: right;">
                    <div style="font-size: 10px; color: #666;">
                        This is a computer-generated receipt.<br>

                    </div>
                </td>
            </tr>
        </table>

        <div class="footer-note">
            Thank you for using ConnectPe services!
        </div>
    </div>

</body>

</html>