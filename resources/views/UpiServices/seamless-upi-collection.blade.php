@extends('layouts.app')

@section('title', 'Seamless UPI Collection')
@section('page-title', 'Seamless UPI Collection')

@section('content')

<div class="accordion mb-3" id="filterAccordion">
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseFilter">Filter</button>
        </h2>
        <div id="collapseFilter" class="accordion-collapse collapse">
            <div class="accordion-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Customer</label>
                        <input type="text" id="filterCustomer" class="form-control" placeholder="Customer Name">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">User</label>
                        <select id="filterUser" class="form-control">
                            <option value="">All Users</option>
                            @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} {{ $user->email }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Any Key</label>
                        <input type="text" id="filterKeyword" class="form-control"
                            placeholder="Txn ID / Order ID / UTR">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select id="filterStatus" class="form-control">
                            <option value="">All</option>
                            <option value="pending">Pending</option>
                            <option value="success">Success</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">From Date</label>
                        <input type="date" id="filterDateFrom" class="form-control">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">To Date</label>
                        <input type="date" id="filterDateTo" class="form-control">
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button class="btn buttonColor w-100" id="applyFilter">Filter</button>
                        <button class="btn btn-secondary w-100" id="resetFilter">Reset</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table id="seamlessTable" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Customer</th>
                        <th>Email</th>
                        <th>Txn ID</th>
                        <th>Order ID</th>
                        <th>Customer TXN ID</th>
                        <th>UTR</th>
                        <th>Amount</th>
                        <th>Fee</th>
                        <th>Tax</th>
                        <th>Net Amount</th>
                        <th>Auto Settlement</th>
                        <th>Webhook</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Updated At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr style="font-weight: bold; background-color: #f8f9fa;">
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script>
    $(function() {

        let table = $('#seamlessTable').DataTable({
            processing: true,
            serverSide: true,
            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "All"]
            ],
            ajax: {
                url: "{{ url('fetch/seamless-upi-collection') }}",
                type: "POST",
                data: function(d) {
                    d._token = "{{ csrf_token() }}";
                    d.any_key = $('#filterKeyword').val();
                    d.cust_name = $('#filterCustomer').val();
                    d.user_id = $('#filterUser').val();
                    d.status = $('#filterStatus').val();
                    d.date_from = $('#filterDateFrom').val();
                    d.date_to = $('#filterDateTo').val();
                }
            },

            columns: [

                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },

                {
                    data: 'user',
                    orderable: false,
                    searchable: false,
                    render: function(data) {
                        if (!data) return '-';

                        return `
                                <div>
                                    <div class="fw-semibold">${data.name ?? '-'}</div>
                                    <small class="text-muted">${data.email ?? '-'}</small>
                                </div>
                            `;
                    }
                },

                {
                    data: 'cust_name'
                },
                {
                    data: 'cust_email'
                },
                {
                    data: 'cust_txn_id'
                },
                {
                    data: 'connectpe_order_id'
                },
                {
                    data: 'cust_txn_id'
                },
                {
                    data: 'utr'
                },
                {
                    data: 'amount'
                },
                {
                    data: 'fee'
                },
                {
                    data: 'tax'
                },
                {
                    data: 'net_amount'
                },

                {
                    data: 'is_auto_settlement',
                    render: function(data) {
                        return data == 1 ?
                            '<span class="badge bg-success">Yes</span>' :
                            '<span class="badge bg-secondary">No</span>';
                    }
                },

                {

                    data: 'is_webhook_send',
                    render: function(data) {
                        return data == 1 ?
                            '<span class="badge bg-success">Sent</span>' :
                            '<span class="badge bg-warning">Pending</span>';

                    }

                },

                {

                    data: 'status',
                    render: function(data) {
                        let badge = 'secondary';
                        if (data == 'success') badge = 'success';
                        else if (data == 'failed') badge = 'danger';
                        else if (data == 'pending') badge = 'warning';
                        return `<span class="badge bg-${badge}">${data.toUpperCase()}</span>`;

                    }

                },

                {
                    data: 'created_at',
                    render: function(data) {
                        return formatDateTime(data);
                    }
                },
                {
                    data: 'updated_at',
                    render: function(data) {
                        return formatDateTime(data);
                    }
                },
                {
                    data: 'cust_txn_id',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return `
            <button type="button"
                    class="btn btn-sm btn-primary check-status"
                    data-cust-txn-id="${data}"
                    title="Check Status">
                <i class="bi bi-arrow-repeat"></i>
            </button>
        `;
                    }
                }

            ],

            order: [
                [0, 'DESC']
            ],
            footerCallback: function(row, data, start, end, display) {
                let api = this.api();

                let sumColumn = function(index) {
                    let columnData = api.column(index, {
                        page: 'current'
                    }).data();
                    return columnData.reduce(function(a, b) {
                        let x = parseFloat(a) || 0;
                        let y = parseFloat(b) || 0;
                        return x + y;
                    }, 0);
                };

                // Updated column indices based on your columns array
                let amountSum = sumColumn(8);
                let feeSum = sumColumn(9);
                let taxSum = sumColumn(10);
                let netAmountSum = sumColumn(11);

                // Align the "Total:" text right before the Amount column (spans columns 0 to 7: # up to UTR)
                let labelColSpan = 8;

                let footerHtml =
                    `<td colspan="${labelColSpan}" style="text-align: right;"><strong>Total:</strong></td>` +
                    `<td><strong>${amountSum.toFixed(2)}</strong></td>` +
                    `<td><strong>${feeSum.toFixed(2)}</strong></td>` +
                    `<td><strong>${taxSum.toFixed(2)}</strong></td>` +
                    `<td><strong>${netAmountSum.toFixed(2)}</strong></td>`;

                // Fill the remaining tail columns to keep table row structure valid (17 total columns)
                let totalColumns = api.columns().header().length;
                let filledColumns = labelColSpan + 4; // label span + 4 numerical sum columns
                let remainingCols = totalColumns - filledColumns;

                for (let i = 0; i < remainingCols; i++) {
                    footerHtml += `<td></td>`;
                }

                $(api.table().footer()).find('tr').html(footerHtml);
            }

        });
        $('#applyFilter').click(function() {
            table.draw();
        });

        $('#resetFilter').click(function() {
            $('#filterCustomer').val('');
            $('#filterKeyword').val('');
            $('#filterUser').val('');
            $('#filterStatus').val('');
            $('#filterDateFrom').val('');
            $('#filterDateTo').val('');
            table.draw();
        });
    });

    // check status button click
    $(document).on('click', '.check-status', function() {

        let custTxnId = $(this).data('cust-txn-id');

        // Show loading
        Swal.fire({
            title: 'Checking Status...',
            text: 'Please wait',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: "{{ url('/api/payin/checkStatus') }}/" + encodeURIComponent(custTxnId),
            type: "POST",

            data: {
                _token: "{{ csrf_token() }}"
            },

            success: function(response) {

                console.log('Check Status Response:', response);

                if (response.status === true && response.data) {

                    let data = response.data;

                    let status = (data.status || 'pending').toLowerCase();

                    let icon = 'info';
                    let title = 'Transaction Status';

                    if (status === 'success') {
                        icon = 'success';
                        title = 'Transaction Successful';
                    } else if (status === 'failed') {
                        icon = 'error';
                        title = 'Transaction Failed';
                    } else if (status === 'pending') {
                        icon = 'warning';
                        title = 'Transaction Pending';
                    }

                    // Status badge
                    let statusBadge = '';

                    if (status === 'success') {
                        statusBadge = `
                        <span style="
                            display:inline-block;
                            padding:5px 14px;
                            border-radius:20px;
                            background:#d1e7dd;
                            color:#0f5132;
                            font-weight:600;
                            font-size:13px;
                        ">
                            SUCCESS
                        </span>
                    `;
                    } else if (status === 'failed') {
                        statusBadge = `
                        <span style="
                            display:inline-block;
                            padding:5px 14px;
                            border-radius:20px;
                            background:#f8d7da;
                            color:#842029;
                            font-weight:600;
                            font-size:13px;
                        ">
                            FAILED
                        </span>
                    `;
                    } else {
                        statusBadge = `
                        <span style="
                            display:inline-block;
                            padding:5px 14px;
                            border-radius:20px;
                            background:#fff3cd;
                            color:#664d03;
                            font-weight:600;
                            font-size:13px;
                        ">
                            ${(data.status ?? 'PENDING').toUpperCase()}
                        </span>
                    `;
                    }

                    Swal.fire({

                        icon: icon,
                        title: title,
                        width: '470px',

                        html: `

                        <!-- Amount -->
                        <div style="
                            text-align:center;
                            margin:5px 0 20px;
                            padding:14px;
                            border-radius:10px;
                            background:#f8f9fa;
                        ">

                            <div style="
                                font-size:12px;
                                color:#6c757d;
                                margin-bottom:3px;
                            ">
                                Transaction Amount
                            </div>

                            <div style="
                                font-size:28px;
                                font-weight:700;
                            ">
                                ₹${data.amount ?? '-'}
                            </div>

                        </div>


                        <!-- Transaction / Order -->
                        <div style="
                            display:grid;
                            grid-template-columns:1fr 1fr;
                            gap:10px;
                            margin-bottom:10px;
                        ">

                            <!-- Transaction ID -->
                            <div style="
                                padding:11px;
                                border:1px solid #eee;
                                border-radius:8px;
                                text-align:center;
                            ">

                                <div style="
                                    font-size:11px;
                                    color:#6c757d;
                                    margin-bottom:5px;
                                ">
                                    Transaction ID
                                </div>

                                <div style="
                                    font-size:13px;
                                    font-weight:600;
                                    word-break:break-all;
                                ">
                                    ${data.transaction_id ?? '-'}
                                </div>

                            </div>


                            <!-- Order ID -->
                            <div style="
                                padding:11px;
                                border:1px solid #eee;
                                border-radius:8px;
                                text-align:center;
                            ">

                                <div style="
                                    font-size:11px;
                                    color:#6c757d;
                                    margin-bottom:5px;
                                ">
                                    Order ID
                                </div>

                                <div style="
                                    font-size:13px;
                                    font-weight:600;
                                    word-break:break-all;
                                ">
                                    ${data.order_id ?? '-'}
                                </div>

                            </div>

                        </div>


                        <!-- Status -->
                        <div style="
                            display:flex;
                            align-items:center;
                            justify-content:space-between;
                            padding:12px 14px;
                            border:1px solid #eee;
                            border-radius:8px;
                            margin-bottom:10px;
                        ">

                            <span style="
                                font-size:13px;
                                color:#6c757d;
                            ">
                                Transaction Status
                            </span>

                            ${statusBadge}

                        </div>


                        <!-- UTR -->
                        <div style="
                            padding:12px;
                            border:1px solid #eee;
                            border-radius:8px;
                            text-align:center;
                        ">

                            <div style="
                                font-size:11px;
                                color:#6c757d;
                                margin-bottom:5px;
                            ">
                                UTR / Bank Reference
                            </div>

                            <div style="
                                font-size:14px;
                                font-weight:600;
                                word-break:break-all;
                            ">
                                ${data.utr ?? '-'}
                            </div>

                        </div>

                    `,

                        confirmButtonText: 'OK',
                        confirmButtonColor: '#0d6efd',

                        customClass: {
                            popup: 'transaction-status-popup'
                        }

                    });

                } else {

                    Swal.fire({
                        icon: 'error',
                        title: 'Unable to Fetch Status',
                        text: response.message || 'Something went wrong.'
                    });

                }
            },

            error: function(xhr) {

                console.log('Error:', xhr.responseJSON);

                let message = 'Something went wrong while checking transaction status.';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Request Failed',
                    text: message
                });
            }
        });

    });
</script>

@endsection