    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>UPI Payment Slip</title>

        <style>
            body{
                font-family: 'DejaVu Sans', sans-serif;
                font-size: 12px;
                color: #333;
                margin: 0;
                padding: 0;
            }

            .invoice-box { 
                padding: 20px; 
                border: 1px solid #eee; 
                background: #fff;
            }

            /* HEADER */
            .header-table { 
                width: 100%; 
                border-bottom: 2px solid #333; 
                margin-bottom: 20px; 
            }

            .brand-name{
                font-size: 16px;
                font-weight: bold;
            }

            .gst{
                font-size: 10px;
                margin-top: 3px;
            }

            .address{
                font-size: 11px;
                /* margin-top: 6px; */
                line-height: 1.4;
            }

            /* SECTION */
            .section-title{
                font-size: 13px;
                font-weight: bold;
                margin-bottom: 8px;
                padding-bottom: 3px;
                /* border-bottom: 1px solid #ddd; */
            }

            .two-col{
                width: 100%;
            }

            .two-col td{
                vertical-align: top;
                width: 50%;
                padding: 10px;
            }

            .info-table{
                width: 100%;
            }

            .info-table td{
                padding: 4px 0;
            }

            .label{
                color: #777;
                font-size: 11px;
            }

            .value{
                font-weight: bold;
                font-size: 12px;
            }

            .text-success { color: green; }
            .text-danger { color: red; }
            .text-warning { color: orange; }

            .amount{
                color: #0b5ed7;
                font-size: 16px;
                font-weight: bold;
            }

            .footer{
                margin-top: 25px;
                font-size: 10px;
                text-align: center;
                color: #777;
                border-top: 1px dashed #ccc;
                padding-top: 10px;
            }
        </style>
    </head>

    <body>

    <div class="invoice-box">

        <!-- HEADER -->
        <table class="header-table">
            <tr>
                <td>
                    <div class="brand-name">Rafifintech Private Limited</div>
                    <div class="gst">GST NO : 23AANCR7014H1ZE</div>

                    <div class="address">
                        S-260/A, Singapore Green View Premium,<br>
                        Biju Khedi, Indore,<br>
                        Madhya Pradesh - 453771
                    </div>
                </td>
            </tr>
        </table>

        <!-- TWO COLUMN -->
        <table class="two-col">
            <tr>

                <!-- LEFT -->
                <td>

                    <div class="section-title">Transaction Info</div>

                    <table class="info-table">
                        <tr><td class="label">Txn ID:</td><td class="value">{{ $payment->txn_id ?? '---' }}</td></tr>
                        <tr><td class="label">UTR:</td><td class="value">{{ $payment->utr ?? '---' }}</td></tr>

                        <tr>
                            <td class="label">Status:</td>
                            <td class="value 
                                @if($payment->status=='success') text-success 
                                @elseif($payment->status=='failed') text-danger 
                                @else text-warning 
                                @endif">
                                {{ ucfirst($payment->status ?? 'NA') }}
                            </td>
                        </tr>

                        <tr>
                            <td class="label">Date:</td>
                            <td class="value">
                                {{ \Carbon\Carbon::parse($payment->created_at)->format('d M Y, h:i A') }}
                            </td>
                        </tr>

                        {{-- <tr>
                            <td class="label">Type:</td>
                            <td class="value">{{ $payment->type ?? 'UPI' }}</td>
                        </tr> --}}
                    </table>

                    <br>

                    <div class="section-title">Amount Details</div>

                    <table class="info-table">
                        <tr><td class="label">Amount:</td><td class="amount">₹{{ number_format($payment->amount ?? 0, 2) }}</td></tr>
                        <tr><td class="label">Fee:</td><td class="value">₹{{ number_format($payment->fee ?? 0, 2) }}</td></tr>
                        <tr><td class="label">Tax:</td><td class="value">₹{{ number_format($payment->tax ?? 0, 2) }}</td></tr>
                        <tr><td class="label">Net Amount:</td><td class="value">₹{{ number_format($payment->net_amount ?? 0, 2) }}</td></tr>
                    </table>

                </td>

                <!-- RIGHT -->
                <td>

                    <div class="section-title">Payer Details</div>

                    <table class="info-table">
                        <tr><td class="label">Name:</td><td class="value">{{ $payment->cust_name ?? '---' }}</td></tr>
                        <tr><td class="label">Email:</td><td class="value">{{ $payment->cust_email ?? '---' }}</td></tr>
                        <tr><td class="label">Mobile:</td><td class="value">{{ $payment->cust_mobile ?? '---' }}</td></tr>
                        <tr><td class="label">Order ID:</td><td class="value">{{ $payment->connectpe_order_id ?? '---' }}</td></tr>
                        <tr><td class="label">Customer Txn ID:</td><td class="value">{{ $payment->cust_txn_id ?? '---' }}</td></tr>
                    </table>

                    <br>

                    <div class="section-title">Payee & Notes</div>

                    <table class="info-table">
                        <tr><td class="label">Remark:</td><td class="value">{{ $payment->res_message ?? '---' }}</td></tr>
                        {{-- <tr><td class="label">Txn Credited:</td><td class="value">{{ $payment->is_txn_credited ? 'Yes' : 'No' }}</td></tr>
                        <tr><td class="label">NPCI Txn ID:</td><td class="value">{{ $payment->npci_txn_id ?? '---' }}</td></tr> --}}
                    </table>

                </td>

            </tr>
        </table>

        <!-- FOOTER --> 
        <div class="footer">
            <strong>Thank you for your payment!</strong><br>
            This is a computer generated slip. Signature not required.
        </div>

    </div>

    </body>
    </html>