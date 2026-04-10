@extends('layouts.app')

@section('title', 'UPI Collection')
@section('page-title', 'UPI Collection')

@section('content')

    {{-- TOP SUMMARY BOXES --}}
    <div class="row mb-3 g-3">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body">
                    <h6 class="text-muted">Current Balance</h6>
                    <h4 class="fw-bold mb-0">₹ 0.00</h4>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body">
                    <h6 class="text-muted">Settlement Due Today</h6>
                    <h4 class="fw-bold mb-0">₹ 0.00</h4>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body">
                    <h6 class="text-muted">Previous Settlement</h6>
                    <h4 class="fw-bold mb-0">₹ 0.00</h4>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body">
                    <h6 class="text-muted">Upcoming Settlement</h6>
                    <h4 class="fw-bold mb-0">₹ 0.00</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- FILTER --}}
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
                            <label class="form-label">Customer Name</label>
                            <select id="filterUser" class="form-control form-select2">
                                <option value="">All Customers</option>
                                @foreach ($customers as $cust)
                                    <option value="{{ $cust }}">{{ $cust }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Any Key</label>
                            <input type="text" id="filterKeyword" class="form-control" placeholder="UTR / Txn ID">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">From Date</label>
                            <input type="date" class="form-control" id="filterDateFrom">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">To Date</label>
                            <input type="date" class="form-control" id="filterDateTo">
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
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    {{-- SCRIPT --}}
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
                        d.page_type = 'collection';
                        d.any_key = $('#filterKeyword').val();
                        d.cust_name = $('#filterUser').val();
                        // d.status = $('#filterStatus').val();
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
                    },
                    {
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        render: function(data) {
                            return `<a href="/download-slip/${data}" class="btn btn-sm btn-success" title="Download Slip"> <i class="fa fa-download"></i></a>`;
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
                // $('#filterStatus').val('');
                $('#filterDateFrom').val('');
                $('#filterDateTo').val('');
                table.draw();
            });

        });
    </script>

@endsection
