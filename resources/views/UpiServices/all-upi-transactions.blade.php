@extends('layouts.app')

@section('title', 'All UPI Transactions')
@section('page-title', 'All UPI Transactions')

@section('content')

    <div class="accordion mb-3" id="filterAccordion">
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseFilter">
                    Filter
                </button>
            </h2>

            <div id="collapseFilter" class="accordion-collapse collapse">
                <div class="accordion-body">
                    <div class="row g-3 align-items-end">

                        <!-- Customer Dropdown -->
                        <div class="col-md-3">
                            <label class="form-label">User</label>
                            <select id="filterUser" class="form-control">
                                <option value="">All Users</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} {{ $user->email }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Any Key -->
                        <div class="col-md-3">
                            <label class="form-label">Any Key</label>
                            <input type="text" id="filterKeyword" class="form-control" placeholder="UTR / Txn ID">
                        </div>

                        <!-- Status -->
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select id="filterStatus" class="form-control form-select2">
                                <option value="">All</option>
                                <option value="success">Success</option>
                                <option value="failed">Failed</option>
                                <option value="initiated">Initiated</option>
                            </select>
                        </div>

                        <!-- Date -->
                        <div class="col-md-2">
                            <label class="form-label">From Date</label>
                            <input type="date" class="form-control" id="filterDateFrom">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">To Date</label>
                            <input type="date" class="form-control" id="filterDateTo">
                        </div>

                        <!-- Buttons -->
                        <div class="col-md-2 d-flex gap-2">
                            <button class="btn buttonColor w-100" id="applyFilter">Filter</button>
                            <button class="btn btn-secondary w-100" id="resetFilter">Reset</button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="card shadow-sm">
        <div class="card-body pt-4">
            <div class="table-responsive">
                <table id="paymentTable" class="table table-striped table-bordered w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Customer Name</th>
                            <th>Email</th>
                            <th>Cust Txn ID</th>
                            <th>Order ID</th>
                            <th>UTR</th>
                            <th>Amount</th>
                            <th>Fee</th>
                            <th>Tax</th>
                            <th>Net Amount</th>
                            @if (auth()->user()->role_id != 2)
                                <th>Type</th>
                            @endif
                            <th>Status</th>
                            <th>Created At</th>
                            <!-- <th>Action</th> -->
                        </tr>
                    </thead>
                    @if (auth()->user()->role_id != 2)
                        <tfoot>
                            <tr style="font-weight: bold; background-color: #f8f9fa;">
                                <!-- Footer cells will be injected dynamically via footerCallback -->
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>


    <script>
        $(document).ready(function() {

            var table = $('#paymentTable').DataTable({
                processing: true,
                serverSide: true,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                ajax: {
                    url: "{{ url('fetch/upi-collection') }}",
                    type: "POST",
                    data: function(d) {
                        d._token = "{{ csrf_token() }}";
                        d.page_type = 'all';
                        d.any_key = $('#filterKeyword').val();
                        d.user_id = $('#filterUser').val();
                        d.status = $('#filterStatus').val();
                        d.date_from = $('#filterDateFrom').val();
                        d.date_to = $('#filterDateTo').val();
                    }
                },
                columns: [{
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
                    @if (auth()->user()->role_id != 2)
                        {
                            data: 'route',
                            render: function(data, type, row) {
                                return '<b>' + (data ?? '') + '</b>';
                            }
                        },
                    @endif {
                        data: 'status',
                        render: function(data) {
                            let badge = 'secondary';

                            if (data === 'success') badge = 'success';
                            else if (data === 'failed') badge = 'danger';
                            else if (data === 'pending') badge = 'warning';

                            return `<span class="badge bg-${badge}">${data.toUpperCase()}</span>`;
                        }
                    },
                    {
                        data: 'created_at',
                        render: function(data) {
                            return formatDateTime(data);
                        }
                    }
                    //         {
                    //             data: 'id',
                    //             orderable: false,
                    //             searchable: false,
                    //             render: function(data, type, row) {
                    //                 return `
                //     <button type="button"
                //         class="btn btn-sm btn-primary check-status"
                //         data-cust-txn-id="${row.cust_txn_id}"
                //         title="Check Status">
                //         <i class="bi bi-patch-check"></i>
                //     </button>
                // `;
                    //             }
                    //         }
                ],
                order: [],
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

                    let amountSum = sumColumn(6);
                    let feeSum = sumColumn(7);
                    let taxSum = sumColumn(8);
                    let netAmountSum = sumColumn(9);

                    // Align the "Total:" text under the 'UTR' column (Index 5)
                    let labelColSpan = 6;

                    let footerHtml =
                        `<td colspan="${labelColSpan}" style="text-align: right;"><strong>Total:</strong></td>` +
                        `<td><strong>${amountSum.toFixed(2)}</strong></td>` +
                        `<td><strong>${feeSum.toFixed(2)}</strong></td>` +
                        `<td><strong>${taxSum.toFixed(2)}</strong></td>` +
                        `<td><strong>${netAmountSum.toFixed(2)}</strong></td>`;

                    // Fill the remaining tail columns to keep table row structure valid
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
                $('#filterUser').val(null).trigger('change');
                $('#filterKeyword').val('');
                $('#filterStatus').val('');
                $('#filterDateFrom').val('');
                $('#filterUser').val('');
                $('#filterDateTo').val('');
                table.draw();
            });

        });



        // check status button click event
        $(document).on('click', '.check-status', function() {

            let custTxnId = $(this).data('cust-txn-id');
            console.log('Customer Transaction ID:', custTxnId);

            let url = "{{ url('/api/payin/checkStatus') }}/" + custTxnId;
            console.log('Check Status URL:', url);

            $.ajax({
                // url: `/api/payin/checkStatus/${custTxnId}`,
                url: url,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    console.log('Check Status Response:', response);
                },
                error: function(xhr) {
                    console.log('Error:', xhr.responseText);
                }
            });

        });
    </script>

@endsection
