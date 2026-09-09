@extends('layouts.app')

@section('title', 'Seamless UPI Collection')
@section('page-title', 'Seamless UPI Collection')

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

                        <div class="col-md-3">

                            <label class="form-label">Customer</label>

                            <input type="text" id="filterCustomer" class="form-control" placeholder="Customer Name">

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

                            <button class="btn buttonColor w-100" id="applyFilter">

                                Filter

                            </button>

                            <button class="btn btn-secondary w-100" id="resetFilter">

                                Reset

                            </button>

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
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <script>
        $(function() {

            let table = $('#seamlessTable').DataTable({

                processing: true,
                serverSide: true,

                ajax: {
                    url: "{{ url('fetch/seamless-upi-collection') }}",
                    type: "POST",

                    data: function(d) {

                        d._token = "{{ csrf_token() }}";
                        d.any_key = $('#filterKeyword').val();
                        d.cust_name = $('#filterCustomer').val();
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
                            return `<span class="badge bg-${badge}">${data}</span>`;

                        }

                    },

                    {
                        data: 'created_at',
                        render: function(data) {
                            return formatDateTime(data);
                        }
                    }

                ],

                order: [
                    [0, 'DESC']
                ]

            });
            $('#applyFilter').click(function() {
                table.draw();
            });

            $('#resetFilter').click(function() {
                $('#filterCustomer').val('');
                $('#filterKeyword').val('');
                $('#filterStatus').val('');
                $('#filterDateFrom').val('');
                $('#filterDateTo').val('');
                table.draw();
            });
        });
    </script>

@endsection
