@extends('layouts.app')

@section('title', 'Our Services')
@section('page-title', 'Our Services')

@section('page-button')
<div class="row align-items-center mb-2">
    <div class="col-auto ms-auto">
        <button type="button" class="btn buttonColor text-nowrap" data-bs-toggle="modal" data-bs-target="#serviceModal">
            <i class="bi bi-plus fs-6 me-1"></i> Service
        </button>
    </div>
</div>
@endsection

@section('content')

<div class="accordion mb-3" id="filterAccordion">
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingFilter">
            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseFilter" aria-expanded="false" aria-controls="collapseFilter">
                Our Services
            </button>
        </h2>
        <div id="collapseFilter" class="accordion-collapse collapse" aria-labelledby="headingFilter"
            data-bs-parent="#filterAccordion">
            <div class="accordion-body">
                <div class="col-12 col-md-10 col-lg-12">
                    <div class="card shadow-sm">

                        <div class="card-body pt-4">
                            <div class="table-responsive">
                                <table id="servicesTable" class="table table-striped table-bordered table-hover w-100">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Service Name</th>
                                            <th>Slug</th>
                                            <th>Service Type</th>
                                            <th>Activation Allowed</th>
                                            <th>Is_active</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="accordion mb-3" id="userServiceAccordion">
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingFilter">
            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseUserAccordion" aria-expanded="false" aria-controls="collapseUserAccordion">
                Users Services
            </button>
        </h2>
        <div id="collapseUserAccordion" class="accordion-collapse" aria-labelledby="headingFilter"
            data-bs-parent="#userServiceAccordion">
            <div class="accordion-body">
                <div class="row g-3 align-items-end">

                    <div class="col-md-3">
                        <label for="userId" class="form-label">User</label>
                        <select name="userId" id="userId" class="form-control form-select2">
                            <option value="">--Select User--</option>
                            @foreach($users as $value)
                            <option value="{{$value->id}}">{{$value->name}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="globalService" class="form-label">Service</label>
                        <select name="globalService" id="globalService" class="form-control form-select2">
                            <option value="">--Select Service--</option>
                            @foreach($globalServices as $value)
                            <option value="{{$value->id}}">{{$value->service_name}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="date_from" class="form-label">From</label>
                        <input type="date" class="form-control" id="date_from">
                    </div>

                    <div class="col-md-3">
                        <label for="date_to" class="form-label">To</label>
                        <input type="date" class="form-control" id="date_to">
                    </div>



                    <div class="col-md-3 d-flex gap-2">
                        <button class="btn buttonColor " id="applyFilterServicesTable"> Filter</button>
                        <button class="btn btn-secondary" id="resetFilterServicesTable">Reset</button>
                    </div>
                </div>

                <div class="table-responsive mt-3">
                    <table id="userServicesTable" class="table table-striped table-bordered table-hover w-100">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Organization Name</th>
                                <th>Service</th>
                                {{-- <th>Amount</th> --}}
                                <th>API Enable</th>
                                <th>Is Active</th>
                                <th>Status</th>
                                <th>Created at</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="serviceModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="serviceForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="service_name">Service Name</label>
                            <input type="text" class="form-control" name="service_name" id="service_name"
                                value="{{ old('service_name') }}" placeholder="Service Name">
                        </div>
                    </div>
                </div>
                <input type="hidden" id="edit_service_id">
                <input type="hidden" id="form_type" value="add">


                <div class="modal-footer">
                    <button type="submit" class="btn buttonColor">Submit</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {

        var table = $('#servicesTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ url('fetch') }}/global-service/0",
                type: 'POST',
                data: function(d) {
                    d._token = $('meta[name="csrf-token"]').attr('content');
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
                searchPlaceholder: "Search services..."
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
                    data: 'service_name'
                },
                {
                    data: 'slug',
                },
                {
                    data: 'service_type'
                },
                {
                    data: 'is_activation_allowed',
                    render: function(data, type, row) {
                        let checked = data == '1' ? 'checked' : ''; // toggle state
                        return `
                            <div class="form-check form-switch">
                                <input class="form-check-input cursor-pointer" type="checkbox" ${checked}
                                    onchange="changeService('${row.id}', 'is_api_allowed','This Record')">
                            </div>
                        `;
                    },
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'is_active',
                    render: function(data, type, row) {
                        let checked = data == '1' ? 'checked' : ''; // toggle state
                        return `
                            <div class="form-check form-switch">
                                <input class="form-check-input cursor-pointer" type="checkbox" ${checked}
                                    onchange="changeService('${row.id}', 'is_active','Service')">
                            </div>
                        `;
                    },
                    orderable: false,
                    searchable: false
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        return `
                        <button class="btn btn-sm btn-primary"
                            onclick="openEditService(${row.id}, '${row.service_name}')">
                            <i class="fa fa-edit"></i>
                        </button>
                    `;
                    },
                    orderable: false,
                    searchable: false
                }

            ]
        });


        var table = $('#userServicesTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ url('fetch') }}/enabled-services/0",
                type: 'POST',
                data: function(d) {
                    d._token = $('meta[name="csrf-token"]').attr('content');
                    d.user_id = $("#userId").val();
                    d.service_id = $("#globalService").val();
                    d.date_from = $('#date_from').val();
                    d.date_to = $('#date_to').val();
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
                searchPlaceholder: "Search services..."
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
                    data: function(row) {
                        const url = "{{ route('view_user', ['id' => 'id']) }}".replace('id', row.user_id);
                        const userName = row.user?.name || '----';
                        const businessName = row.user?.business?.business_name || '----';
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
                        return row?.service?.service_name || '----'
                    }
                },
                // {
                //     data: function(row) {
                //         const amount = row.transaction_amount ?? 0;
                //         return '₹ ' + amount.toLocaleString('en-IN', {
                //             minimumFractionDigits: 2,
                //             maximumFractionDigits: 2
                //         });
                //     }
                // },
                {
                    data: 'is_api_enable',
                    render: function(data, type, row) {
                        let checked = data == '1' ? 'checked' : ''; // toggle state
                        return `
                            <div class="form-check form-switch">
                                <input class="form-check-input cursor-pointer" type="checkbox" ${checked}
                                    onchange="changeServiceStatus('${row.id}', 'is_api_enable','This Record')">
                            </div>
                        `;
                    }
                },
                {
                    data: 'is_active',
                    render: function(data, type, row) {
                        let checked = data == '1' ? 'checked' : ''; // toggle state
                        return `
                            <div class="form-check form-switch">
                                <input class="form-check-input cursor-pointer" type="checkbox" ${checked}
                                    onchange="changeServiceStatus('${row.id}', 'is_active','Service')">
                            </div>
                        `;
                    }
                },
                {
                    data: 'status',
                    render: function(data) {
                        return `<span class="text-success fw-bold">${formatStatus(data)}</span>`
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

        $('#applyFilterServicesTable').on('click', function() {
            table.ajax.reload();
        });

        $('#resetFilterServicesTable').on('click', function() {
            $('#userId').val('').trigger('change');
            $('#globalService').val('').trigger('change');
            $('#date_from').val('');
            $('#date_to').val('');
            table.ajax.reload();
        });
    });

    function changeServiceStatus(id, type, text = 'This Record') {
        Swal.fire({
            title: 'Are you sure to change status of ' + text + '?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes',
            cancelButtonText: 'No'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('active_user_service_status') }}",
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        service_id: id,
                        type: type
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message ?? 'Status updated successfully',
                            timer: 2000,
                            showConfirmButton: true
                        });

                        setTimeout(() => {
                            location.reload();
                        }, 2000);
                    },
                    error: function(xhr) {
                        let title = 'Error';
                        let message = 'Something went wrong!';

                        if (xhr.status === 422) {
                            title = 'Validation Error';

                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let firstKey = Object.keys(xhr.responseJSON.errors)[0];
                                message = xhr.responseJSON.errors[firstKey][0];
                            }
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: title,
                            html: message,
                            timer: 2000,
                            showConfirmButton: true
                        });

                        setTimeout(() => {
                            location.reload();
                        }, 3000);
                    }

                });
            }
        });
    }


    function changeService(id, type, text = 'This Record') {
        Swal.fire({
            title: 'Are you sure to change status of ' + text + '?',
            // text: "You will be logged out from your account!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes',
            cancelButtonText: 'No'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{route('admin.service_toggle')}}",
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        service_id: id,
                        type: type
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message ?? 'Status updated successfully',
                            timer: 2000,
                            showConfirmButton: true
                        });
                    },
                    error: function(xhr) {
                        let title = 'Error';
                        let message = 'Something went wrong!';

                        if (xhr.status === 422) {
                            title = 'Validation Error';

                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let firstKey = Object.keys(xhr.responseJSON.errors)[0];
                                message = xhr.responseJSON.errors[firstKey][0];
                            }
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: title,
                            html: message,
                            timer: 2000,
                            showConfirmButton: true
                        });
                    }

                });
            }
        });
    }
</script>


<script>
    $(document).ready(function() {

        $('#serviceForm').on('submit', function(e) {
            e.preventDefault();

            let serviceName = $('#service_name').val();
            let serviceId = $('#edit_service_id').val();
            let formType = $('#form_type').val();

            if (serviceName.trim() === '') {
                Swal.fire('Error', 'Service name is required', 'error');
                return;
            }

            let url = "{{ route('admin.service.add') }}";

            if (formType === 'edit') {
                url = "{{ route('admin.service.edit',['id' => ':id']) }}";
                url = url.replace(':id', serviceId)
            }

            $.ajax({
                url: url,
                type: "POST",
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    service_name: serviceName
                },
                success: function(response) {
                    if (response.status) {

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        $('#serviceModal').modal('hide');
                        $('#serviceForm')[0].reset();
                        $('#form_type').val('add');
                        $('#edit_service_id').val('');
                        $('#servicesTable').DataTable().ajax.reload(null, false);
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
        });
    });

    function openEditService(id, name) {
        $('#service_name').val(name);
        $('#edit_service_id').val(id);
        $('#form_type').val('edit');

        $('#serviceModalTitle').text('Edit Service');
        $('#serviceModal').modal('show');
    }
</script>
<script>
    $('#serviceModal').on('hidden.bs.modal', function() {
        $('#serviceForm')[0].reset();
        $('#form_type').val('add');
        $('#edit_service_id').val('');
        $('.modal-title').text('Create Service');
    });
</script>

@endsection