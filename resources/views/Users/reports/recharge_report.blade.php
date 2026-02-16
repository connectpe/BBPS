@extends('layouts.app')

@section('title', 'Recharge Report')
@section('page-title', 'Recharge Report')

@section('page-button')
<img src="{{ asset('assets/image/Logo/bharat-connect-logo.jpg') }}"
    alt="" style="width: 84px; z-index: 1060;">
@endsection
@section('content')

@php
$role = Auth::user()->role_id;
@endphp

<div class="accordion mb-3" id="filterAccordion">
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingFilter">
            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseFilter" aria-expanded="false" aria-controls="collapseFilter">
                Filter
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
                    @if(Auth::user()->role_id == 1)
                    <div class="col-md-3">
                        <label for="filterUser" class="form-label">User</label>
                        <select id="filterUser" class="form-control form-select2">
                            <option value="">--Select User--</option>
                            @foreach ($users as $value)

                            <option value="{{ $value->id }}">{{ $value->name }} ({{ $value->email }})</option>

                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="col-md-3">
                        <label for="filterreferenceId" class="form-label">ReferenceId</label>
                        <input type="text" class="form-control" id="filterreferenceId"
                            placeholder="Enter ReferenceId">
                    </div>

                    <div class="col-md-3">
                        <label for="filterStatus" class="form-label">Status</label>
                        <select class="form-select form-select2" id="filterStatus">
                            <option value="">--select--</option>
                            <option value="queued">Queued</option>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="processed">Processed</option>
                            <option value="failed">Failed</option>
                            <option value="reversed">Reversed</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filterCommonId" class="form-label">Search Id</label>
                        <input type="text" class="form-control" id="filterCommonId" placeholder="Payment / ConnectPe / Request ID">
                    </div>
                    <div class="col-md-3">
                        <label for="filterMobile" class="form-label">Mobile No</label>
                        <input type="text" class="form-control" id="filterMobile" placeholder="Enter Mobile Number" maxlength="10">
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
                            <th>S.No</th>
                            @if($role == 1 || $role == 4)
                            <th>Organization Name</th>
                            @else
                            <th> </th>
                            @endif
                            <th>Operator</th>
                            <th>Amount</th>
                            <th>Mobile No</th>
                            <th>Transaction Type</th>
                            <th>Reference No</th>
                            <th>Payment Reference ID</th>
                            <th>ConnectPe ID</th>
                            <th>Transaction ID</th>
                            <th>Created at</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                </table>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {

        let role = "{{$role}}";
        var table = $('#rechargeTable').DataTable({
            processing: true,
            serverSide: true,
            columnDefs: [{
                targets: 1,
                visible: (role == 1 || role == 4)
            }],
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
                    d.common_id = $('#filterCommonId').val();
                    d.mobile_number = $('#filterMobile').val();


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
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        return meta.settings._iDisplayStart + meta.row + 1;
                    }
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        let url = "{{ route('view_user', ['id' => 'id']) }}".replace('id', row.user_id);
                        const userName = row?.user?.name;
                        const businessName = row?.user?.business?.business_name;

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
                    render: function(data, type, row) {
                        let operatorName = row.operator ? row.operator.name : '';
                        let circleName = row.circle ? row.circle.name : '';
                        if (!operatorName) return '----';
                        if (circleName) {
                            return circleName + ' ' + '[' + operatorName + ']';;
                        }
                        return operatorName;
                    }
                },

                // {
                //     data: 'operator',
                //     render: function(data) {
                //         if (!data) return '----';
                //         return `${data.name} [${data.code}]`;
                //     }
                // },
                // {
                //     data: 'circle',
                //     render: function(data, type, row) {
                //         const circle = `${data.name} [${data.code}]`;
                //         return `${circle} `;
                //     }
                // },
                {
                    data: 'amount'
                },
                {
                    data: 'mobile_number'
                },
                {
                    data: 'transaction_type'
                },
                {
                    data: 'reference_number'
                },
                {
                    data: 'payment_ref_id'
                },
                {
                    data: 'connectpe_id'
                },
                {
                    data: 'request_id'
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
                            queued: 'primary', // blue – in queue
                            pending: 'secondary', // gray – waiting
                            processing: 'warning', // yellow – in progress
                            processed: 'success', // green – done
                            failed: 'danger', // red – error
                            reversed: 'info', // blue – rolled back
                        };
                        const color = colors[data] || 'secondary';
                        return `<span class="badge bg-${color}">${formatStatus(data)}</span>`;
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {


                        if (row.status === 'processed') {
                            let url = "{{ route('recharge.invoice.download', ':id') }}"
                                .replace(':id', row.id);

                            return `
                <a href="${url}" 
                   class="btn btn-sm btn-success">
                    <i class="bi bi-download"></i> Invoice
                </a>
            `;
                        }

                        return `<span class="text-muted">----</span>`;
                    }
                }

            ]
        });
        $('#filterDateFrom').on('change', function() {
            let from = $(this).val();
            $('#filterDateTo').attr('min', from);
            if ($('#filterDateTo').val() && $('#filterDateTo').val() < from) {
                $('#filterDateTo').val('');
            }
        });

        $('#applyFilter').on('click', function() {
            table.ajax.reload();
        });

        $('#resetFilter').on('click', function() {
            $('#filterUser').val('').trigger('change');;
            $('#filterreferenceId').val('');
            $('#filterStatus').val('').trigger('change');;
            $('#filterDateFrom').val('');
            $('#filterDateTo').val('');
            $('#filterDateTo').val('').removeAttr('min');
            $('#filterMobile').val('');
            $('#filterCommonId').val('').trigger('input').trigger('change');
            table.ajax.reload();
        });
    });
</script>
@endsection