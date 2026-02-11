@extends('layouts.app')

@section('title', 'NSDL Payments')
@section('page-title', 'NSDL Payments')

@section('content')

<div class="accordion mb-3" id="filterAccordion">
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingFilter">
            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilter" aria-expanded="false" aria-controls="collapseFilter">
                Filter
            </button>
        </h2>
        <div id="collapseFilter" class="accordion-collapse collapse" aria-labelledby="headingFilter" data-bs-parent="#filterAccordion">
            <div class="accordion-body">
                <div class="row g-2 align-items-end">

                    <div class="col-md-3">
                        <label for="filterUser" class="form-label">User</label>
                        <select name="filterUser" id="filterUser" class="form-control form-select2">
                            <option value="">--Select User--</option>
                            @foreach($users as $value)
                            <option value="{{$value->id}}">{{$value->name}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="filterService" class="form-label">Service</label>
                        <select name="filterService" id="filterService" class="form-control form-select2">
                            <option value="">--Select Service--</option>
                            @foreach($globalServices as $service)
                            <option value="{{$service->id}}">{{$service->service_name}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="mobile" class="form-label">Mobile</label>
                        <input type="number" class="form-control" id="mobile" placeholder="Mobile">
                    </div>


                    <div class="col-md-3">
                        <label for="filterStatus" class="form-label">Status</label>
                        <select class="form-select form-select2" id="filterStatus">
                            <option value="">--Select Status--</option>
                            <option value="initiated">Initiated</option>
                            <option value="pending">Pending</option>
                            <option value="success">Success</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="transactionNo" class="form-label">TXN Number</label>
                        <input type="text" class="form-control" id="transactionNo" placeholder="TXN Number">
                    </div>

                    <div class="col-md-3">
                        <label for="utrNumber" class="form-label">UTR Number</label>
                        <input type="text" class="form-control" id="utrNumber" placeholder="UTR Number">
                    </div>

                    <div class="col-md-3">
                        <label for="orderId" class="form-label">Order ID</label>
                        <input type="text" class="form-control" id="orderId" placeholder="Order ID">
                    </div>

                    <div class="col-md-3">
                        <label for="filterDateFrom" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="filterDateFrom">
                    </div>

                    <div class="col-md-3">
                        <label for="filterDateTo" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="filterDateTo">
                    </div>

                    <div class="col-12 d-flex gap-2 justify-content-center">
                        <button class="btn buttonColor " id="applyFilter"> Filter</button>
                        <button class="btn btn-secondary" id="resetFilter">Reset</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table id="nsdlTxnTable" class="table table-bordered table-striped w-100">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Organization Name</th>
                        <th>Service</th>
                        <th>Amount</th>
                        <th>Transaction ID</th>
                        <th>Mobile No</th>
                        <th>UTR</th>
                        <th>Status</th>
                        <th>Order ID</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>



<script>
    $(document).ready(function() {

        var table = $('#nsdlTxnTable').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            ajax: {
                url: "{{ url('fetch/nsdl-payment/0/all') }}",
                type: "POST",
                data: function(d) {
                    d._token = "{{ csrf_token() }}";
                    d.user_id = $("#filterUser").val();
                    d.service_id = $("#filterService").val();
                    d.transaction_id = $('#transactionNo').val();
                    d.utr = $('#utrNumber').val();
                    d.order_id = $('#orderId').val();
                    d.mobile_no = $('#mobile').val();
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
                    data: null,
                    render: function(row, type) {
                        let url = "{{ route('view_user', ['id' => ':id']) }}".replace(':id', row.user_id);
                        const businessName = row?.user?.business?.business_name || '----';
                        const userName = row?.user?.name || '----';
                        return `
                                <a href="${url}" class="text-primary fw-semibold text-decoration-none">
                                    ${userName ?? '----'} <br/>
                                    [${businessName ?? '----'}]
                                </a>
                            `;
                    }
                },
                {
                    data: null,
                    render: function(row, type) {
                        const serviceName = row?.service?.service_name ?? '-';
                        return `<span>${serviceName}</span>`
                    },
                },
                {
                    data: "amount",
                    defaultContent: "-"
                },
                {
                    data: "transaction_id",
                    defaultContent: "-"
                },
                {
                    data: "mobile_no",
                    defaultContent: "-"
                },
                {
                    data: "utr",
                    defaultContent: "-"
                },
                {
                    data: "status",
                    render: function(data) {
                        if (!data) return '-';

                        let status = data.toLowerCase();

                        if (status === 'success') {
                            return `<span class="badge bg-success text-uppercase">${data}</span>`;
                        }
                        if (status === 'pending' || status === 'initiated') {
                            return `<span class="badge bg-warning text-dark text-uppercase">${data}</span>`;
                        }
                        if (status === 'failed') {
                            return `<span class="badge bg-danger text-uppercase">${data}</span>`;
                        }

                        return `<span class="badge bg-secondary text-uppercase">${data}</span>`;
                    }
                },
                {
                    data: "order_id",
                    defaultContent: "-"
                },
                {
                    data: "created_at",
                    render: function(data) {
                        return formatDateTime(data);
                    }
                }
            ],
            order: [
                [8, "desc"]
            ]
        });


        // Apply filter
        $('#applyFilter').on('click', function() {
            table.ajax.reload();
        });

        // Reset filter
        $('#resetFilter').on('click', function() {
            $("#filterUser").val('').trigger('change');
            $("#filterService").val('').trigger('change');
            $('#filterStatus').val('').trigger('change');
            $('#filterDateFrom').val('');
            $('#filterDateTo').val('');
            $("#transactionNo").val('')
            $("#utrNumber").val('')
            $("#orderId").val('')
            $("#mobile").val('')
            table.ajax.reload();
        });

    });
</script>

@endsection