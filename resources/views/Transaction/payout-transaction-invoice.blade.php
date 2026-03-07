<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payout Receipt</title>
    <style>
        body{
         font-family: 'DejaVu Sans', sans-serif;
         font-size: 12px; color: #333; margin: 0; padding: 0;
    }
        .invoice-box { 
            padding: 20px; 
            border: 1px solid #eee; 
            background: #fff;
        }
        .header-table { width: 100%; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .brand-name { font-size: 24px; font-weight: bold; color: #000; }
        .company-details { text-align: right; font-size: 10px; line-height: 1.4; }
        
        .txn-info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .txn-info-table th { text-align: left; font-size: 10px; color: #666; text-transform: uppercase; padding-bottom: 5px; }
        .txn-info-table td { font-size: 11px; font-weight: bold; padding-bottom: 15px; border-bottom: 1px solid #f4f4f4; }

        .footer-table { width: 100%; margin-top: 20px; }
        .thank-you { font-size: 14px; font-weight: bold; }
        .total-section { text-align: right; }
        .total-row { font-size: 14px; font-weight: bold; border-top: 2px solid #333; padding-top: 10px; }
        .text-success { color: green; }
        
    </style>
</head>
<body>
    <div class="invoice-box">
        <table class="header-table">
            <tr>
                <td class="brand-name">
                     <img src="{{ app()->environment('local')   ? public_path('assets/image/Logo/b-assured-logo.jpg')  : asset('assets/image/Logo/b-assured-logo.jpg') }}" alt="Logo" style="width: 70px;">
                </td>
                <td class="company-details">
                    <strong>{{ $order->user->business->business_name ?? 'Business Name' }}</strong><br>
                    {{ $order->user->name }} | {{ $order->user->email }}<br>
                    Date: {{ \Carbon\Carbon::parse($order->created_at)->format('d M Y') }} | {{ \Carbon\Carbon::parse($order->created_at)->format('h:i A') }}
                </td>
            </tr>
        </table>

        <table class="txn-info-table">
            <tr>
                <th>Status</th>
                <th>ConnectPe ID</th>
                <th>Transaction No</th>
                <th>UTR No</th>
            </tr>
            <tr>
                <td class="text-success">{{ strtoupper($order->status) }}</td>
                <td>{{ $order->connectpe_id ?? '---' }}</td>
                <td>{{ $order->transaction_no ?? '---' }}</td>
                <td>{{ $order->utr_no ?? '---' }}</td>
            </tr>
            <tr>
                <th>Account No</th>
                <th>IFSC Code</th>
                <th>Bene Name</th>
                <th>Payout Mode</th>
            </tr>
            <tr>
                <td>{{ $order->account_no ?? '---' }}</td>
                <td>{{ $order->ifsc_code ?? '---' }}</td>
                <td>{{ $order->beneficiary_name ?? '---' }}</td>
                <td>{{ strtoupper($order->mode ?? '---') }}</td>
            </tr>
            <tr>
                <th colspan="2">Remark</th>
                <th colspan="2" style="text-align: right;">Amount</th>
            </tr>
            <tr>
                <td colspan="2">{{ $order->remark ?? 'Office Exp' }}</td>
                <td colspan="2" style="text-align: right; font-size: 16px;">₹{{ number_format($order->amount, 2) }}</td>
            </tr>
        </table>

        <table class="footer-table">
            <tr>
                <td>
                    <p class="thank-you">Thank you for Transacting!</p>
                    <p style="font-size: 9px; color: #888;">This is a system generated receipt hence does not require any signature.</p>
                </td>
                {{-- <td class="total-section">
                    <table align="right">
                        <tr>
                            <td style="padding-right: 20px;">Total Amount</td>
                            <td>₹{{ number_format($order->amount, 2) }}</td>
                        </tr>
                        <tr class="total-row">
                            <td style="padding-right: 20px;">Grand Total</td>
                            <td>₹{{ number_format($order->amount, 2) }}</td>
                        </tr>
                    </table>
                </td> --}}
            </tr>
        </table>
    </div>
</body>
</html>