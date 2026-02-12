@extends('layouts.app')

@section('title', 'Request Services')
@section('page-title', 'Request Services')

@section('content')

<div class="accordion mb-3" id="filterAccordion">
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseFilter">
                Filter Service Requests
            </button>
        </h2>

        <div id="collapseFilter" class="accordion-collapse collapse">
            <div class="accordion-body">
                <div class="row g-3 align-items-end">

                    <div class="col-md-3">
                        <label for="filterUser" class="form-label">User</label>
                        <select name="filterUser" id="filterUser" class="form-control form-select2">
                            <option value="">--Select User--</option>
                            @foreach($users as $value)
                            <option value="{{$value->id}}">{{$value->name}} ({{ $value->name }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="filterService" class="form-label">Service</label>
                        <select name="filterService" id="filterService" class="form-control form-select2">
                            <option value="">--Select Service--</option>
                            @foreach($globalServices as $value)
                            <option value="{{$value->id}}">{{$value->service_name}}</option>
                            @endforeach
                        </select>
                    </div>


                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select id="filterStatus" class="form-select form-select2">
                            <option value="">All</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">From Date</label>
                        <input type="date" id="filterDateFrom" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">To Date</label>
                        <input type="date" id="filterDateTo" class="form-control">
                    </div>

                    <div class="col-md-3 d-flex gap-2">
                        <button type="button" class="btn buttonColor" id="applyFilter">Filter</button>
                        <button type="button" class="btn btn-secondary" id="resetFilter">Reset</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-12">
    <div class="card shadow-sm">
        <div class="card-body pt-4">
            <div class="table-responsive">
                <table id="serviceRequestTable" class="table table-striped table-bordered table-hover w-100">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Organization Name</th>
                            <th>Service</th>
                            <th>Status</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>



<script>
    $(document).ready(function() {

        var table = $('#serviceRequestTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{url('fetch')}}/serviceRequest/0",
                type: 'POST',
                data: function(d) {
                    d._token = $('meta[name="csrf-token"]').attr('content');
                    d.user_id = $('#filterUser').val();
                    d.service_id = $('#filterService').val();
                    d.email = $('#filterEmail').val();
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
                searchPlaceholder: "Search users..."
            },

            columns: [{
                    data: null,
                    render: function(data, type, row, meta) {
                        return meta.row + 1;
                    }
                },
                {
                    data: function(row) {
                        let url = "{{route('view_user',['id' => 'id'])}}".replace('id', row.user_id);
                        const userName = row.user?.name || '----'
                        const businessName = row.user?.business?.business_name || '----'
                        return `
                                <a href="${url}" class="text-primary fw-semibold text-decoration-none">
                                    ${userName ?? '----'} <br/>
                                    [${businessName ?? '----'}]
                                </a>
                            `;
                    }
                },
                {
                    data: function(row) {
                        return row.service?.service_name || '----'
                    }
                },
                {
                    data: 'status',
                    render: function(data, type, row) {

                        const colors = {
                            'approved': 'success',
                        }

                        if (data === 'pending') {
                            return `
                                <select class="status-dropdown form-control" onchange="approveRejectService(${row.id})">
                                    <option value="pending" selected>Pending</option>
                                    <option value="approved">Approved</option>
                                </select>
                            `;
                        }

                        return `<span class="status-text fw-bold text-${colors[data]} ${data}">${formatStatus(data)}</span>`;
                    }
                },
                {
                    data: 'created_at',
                    render: function(data) {
                        return formatDateTime(data)
                    }
                }
            ]
        });


        // Apply filter
        $('#applyFilter').on('click', function() {
            table.ajax.reload();
        });

        $('#resetFilter').on('click', function() {
            $('#filterUser').val('').trigger('change');;
            $('#filterService').val('').trigger('change');;
            $('#filterStatus').val('').trigger('change');;
            $('#filterDateFrom').val('');
            $('#filterDateTo').val('');
            table.ajax.reload();


        });


        $(document).on('click', '.approve-btn', function() {

            let id = $(this).data('id');
            let status = $(this).data('status');

        });

    });


    function approveRejectService(serviceId) {

        const url = "{{route('service_request_approve_reject')}}";

        Swal.fire({
            title: 'Approve Request',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {

                $.ajax({
                    url: url,
                    type: "POST",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        serviceId: serviceId,
                    },
                    success: function(response) {
                        if (response.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: true
                            });

                            setTimeout(() => {
                                location.reload()
                            }, 2000);

                        }
                    },
                    error: function(xhr) {
                        let message = 'Something went wrong';
                        if (xhr.status === 422 && xhr.responseJSON?.errors?.service_name) {
                            message = xhr.responseJSON.errors.service_name[0];
                        } else if (xhr.responseJSON?.message) {
                            message = xhr.responseJSON.message;
                        }
                        Swal.fire('Error', message, 'error');
                    }
                });



            }
        });
    }
</script>

@endsection