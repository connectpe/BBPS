@extends('layouts.app')

@section('title', 'Recharge Report')
@section('page-title', 'Recharge Report')

@section('content')


<div class="accordion mb-3" id="filterAccordion">
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingFilter">
            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseFilter" aria-expanded="false" aria-controls="collapseFilter">
                Filter Users
            </button>
        </h2>
        <div id="collapseFilter" class="accordion-collapse collapse" aria-labelledby="headingFilter"
            data-bs-parent="#filterAccordion">
            <div class="accordion-body">
                <div class="row g-3 align-items-end">
                    <!-- <div class="col-md-3">
                                        <label for="filterName" class="form-label">OrderId</label>
                                        <input type="text" class="form-control" id="filterOrderId" placeholder="Enter OrderId">
                                    </div> -->

                    <div class="col-md-4">
                        <label for="filterUser" class="form-label">User</label>
                        <select id="filterUser" class="form-control">
                            <option value="">--Select User--</option>
                            @foreach ($users as $value)
                            <option value="{{ $value->id }}">{{ $value->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="filterreferenceId" class="form-label">ReferenceId</label>
                        <input type="text" class="form-control" id="filterreferenceId"
                            placeholder="Enter ReferenceId">
                    </div>

                    <div class="col-md-4">
                        <label for="filterStatus" class="form-label">Status</label>
                        <select class="form-select" id="filterStatus">
                            <option value="">--select--</option>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="processed">Processed</option>
                            <option value="failed">Failed</option>
                            <option value="reversed">Reversed</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="filterDateFrom" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="filterDateFrom">
                    </div>

                    <div class="col-md-3">
                        <label for="filterDateTo" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="filterDateTo">
                    </div>


                    <div class="col-md-3 d-flex gap-2">
                        <button class="btn buttonColor " id="applyFilter"> Filter</button>
                        <button class="btn btn-secondary" id="resetFilter">Reset</button>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>


<div class="col-12 col-md-10 col-lg-12">
    <div class="card shadow-sm">

        <div class="card-body pt-4">
            <!-- Table -->
            <div class="table-responsive">
                <table id="rechargeTable" class="table table-striped table-bordered table-hover w-100">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Operator</th>
                            <th>Circle</th>
                            <th>Amount</th>
                            <th>Transaction Type</th>
                            <th>Referene No</th>
                            <th>Created at</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {

    
        var table = $('#rechargeTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ url('fetch') }}/transactions/0",
                type: "POST",
                data: function(d) {
                    d._token = $('meta[name="csrf-token"]').attr('content');
                    d.user_id = $('#filterUser').val();
                    d.reference_number = $('#filterreferenceId').val();
                    d.status = $('#filterStatus').val();
                    d.date_from = $('#filterDateFrom').val();
                    d.date_to = $('#filterDateTo').val();
                }
            },
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50],
            responsive: false,
            dom: "<'row mb-2'<'col-sm-4'l><'col-sm-4'f><'col-sm-4 text-end'B>>" +
                "<'row'<'col-12'tr>>" +
                "<'row mt-2'<'col-sm-6'i><'col-sm-6'p>>",
            buttons: [{
                    extend: 'excelHtml5',
                    text: 'Excel',
                    className: 'btn buttonColor btn-sm'
                },
                {
                    extend: 'pdfHtml5',
                    text: 'PDF',
                    className: 'btn buttonColor btn-sm'
                }
            ],
            language: {
                searchPlaceholder: "Search Transactions..."
            },
            columns: [{
                    data: 'id'
                },
                {
                    data: 'user.name',
                    render: function(data, type, row) {
                        const userRole = "{{ Auth::user()->role_id }}";
                        if (userRole == 1) {
                            let url = "{{ route('view_user', ['id' => 'id']) }}".replace('id',
                                row.user_id);
                            return `<a href="${url}" class="text-primary fw-semibold text-decoration-none">${data ?? '----'}</a>`;
                        }
                        return data ?? '----';
                    }
                },
                {
                    data: 'operator',
                    render: function(data) {
                        if (!data) return '----';
                        return `${data.name} [${data.code}]`;
                    }
                },
                {
                    data: 'circle',
                    render: function(data, type, row) {
                        const circle = `${data.name} [${data.code}]`;
                        return `${circle} `;
                    }
                },
                {
                    data: 'amount'
                },
                {
                    data: 'transaction_type'
                },
                {
                    data: 'reference_number'
                },
                {
                    data: 'created_at',
                    render: function(data) {
                        return formatDateTime(data);
                    }
                },
                {
                    data: 'status',
                    render: function(data) {
                        const colors = {
                            pending: 'secondary', // gray – waiting
                            processing: 'warning', // yellow – in progress
                            processed: 'success', // green – done
                            failed: 'danger', // red – error
                            reversed: 'info', // blue – rolled back
                        };
                        const color = colors[data] || 'secondary';
                        return `<span class="badge bg-${color}">${formatStatus(data)}</span>`;
                    }
                }
            ]
        });

        $('#applyFilter').on('click', function() {
            table.ajax.reload();
        });

        $('#resetFilter').on('click', function() {
            $('#filterUser').val('');
            $('#filterreferenceId').val('');
            $('#filterStatus').val('');
            $('#filterDateFrom').val('');
            $('#filterDateTo').val('');
            table.ajax.reload();
        });
    });
</script>
@endsection