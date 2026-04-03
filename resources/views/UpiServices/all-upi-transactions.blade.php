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
                            <label class="form-label">Customer Name</label>
                            <select id="filterUser" class="form-control form-select2">
                                <option value="">All Customers</option>
                                @foreach ($customers as $cust)
                                    <option value="{{ $cust }}">{{ $cust }}</option>
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
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>


    <script>
        $(document).ready(function() {

            var table = $('#paymentTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ url('fetch/upi-collection') }}",
                    type: "POST",
                    data: function(d) {
                        d._token = "{{ csrf_token() }}";
                        d.page_type = 'all';
                        d.any_key = $('#filterKeyword').val();
                        d.cust_name = $('#filterUser').val();
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
                            data: 'type'
                        },
                    @endif {
                        data: 'status',
                        render: function(data) {
                            let badge = 'secondary';

                            if (data === 'success') badge = 'success';
                            else if (data === 'failed') badge = 'danger';
                            else if (data === 'initiated') badge = 'warning';

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
                $('#filterUser').val(null).trigger('change');
                $('#filterKeyword').val('');
                $('#filterStatus').val('');
                $('#filterDateFrom').val('');
                $('#filterDateTo').val('');
                table.draw();
            });

        });
    </script>

@endsection
