@extends('layouts.app')

@section('title', 'Providers')
@section('page-title', 'Providers')

@section('page-button')
<div class="row align-items-center mb-2">
    <div class="col-auto ms-auto">
        <button type="button" class="btn buttonColor text-nowrap" data-bs-toggle="modal"
            data-bs-target="#providerModal">
            <i class="fa fa-plus"></i> Provider
        </button>
    </div>
</div>
@endsection

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
                        <label for="filterService" class="form-label">Service</label>
                        <select name="filterService" id="filterService" class="form-control form-select2">
                            <option value="">--Select Service--</option>
                            @foreach($globalServices as $value)
                            <option value="{{$value->id}}">{{$value->service_name}}</option>
                            @endforeach
                        </select>
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

<div class="col-12 col-md-10 col-lg-12">
    <div class="card shadow-sm">

        <div class="card-body pt-4">
            <div class="table-responsive">
                <table id="providerTable" class="table table-striped table-bordered table-hover w-100">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Service Name</th>
                            <th>Provider Name</th>
                            <th>Provider Slug</th>
                            <th>Is_active</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="providerModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="providerForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="providerModalTitle">Add Provider</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-md-12">
                            <label for="service_id">Service<span class="text-danger">*</span> </label>
                            <select name="service_id" id="service_id" class="form-control form-select2">
                                <option value="">--Select Service--</option>
                                @foreach($globalServices as $service)
                                <option value="{{$service->id}}" {{$service->id == old('service_id') ? 'selected' :
                                    ''}}>{{$service->service_name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label for="provider_name">Provider Name<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="provider_name" id="provider_name"
                                value="{{ old('provider_name') }}" placeholder="Provider Name">
                        </div>
                    </div>
                </div>

                <input type="hidden" id="edit_provider_id">
                <input type="hidden" id="form_type" value="add">

                <div class="modal-footer">
                    <button type="submit" class="btn buttonColor">Submit</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function () {

        var table = $('#providerTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ url('fetch') }}/providers/0",
                type: 'POST',
                data: function (d) {
                    d._token = $('meta[name="csrf-token"]').attr('content');
                    d.service_id = $("#filterService").val()
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
                searchPlaceholder: "Search Providers..."
            },

            columns: [{
                data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row, meta) {
                    return meta.settings._iDisplayStart + meta.row + 1;
                }
            },
            {
                data: 'service.service_name',
                render: function (data) {
                    return data || '----'
                }
            },
            {
                data: 'provider_name',
                render: function (data) {
                    return data || '----'
                }
            },
            {
                data: 'provider_slug',
                render: function (data) {
                    return data || '----'
                }
            },
            {
                data: 'is_active',
                render: function (data, type, row) {
                    let checked = data == '1' ? 'checked' : ''; // toggle state
                    return `
                            <div class="form-check form-switch">
                                <input class="form-check-input cursor-pointer" type="checkbox" ${checked}
                                    onchange="changeStatus(this,'${row.id}')">
                            </div>
                        `;
                },
                orderable: false,
                searchable: false
            },
            {
                data: null,
                render: function (data, type, row) {
                    return `
                            <button class="btn btn-sm btn-primary"
                                onclick="openEditService(${row.id}, '${row.provider_name}','${row.service_id}')">
                                <i class="fa fa-edit"></i>
                            </button>
                        `;
                }
            }
            ]
        });

        $('#applyFilter').on('click', function () {
            table.ajax.reload();
        });

        $('#resetFilter').on('click', function () {
            $('#filterService').val('').trigger('change');
            table.ajax.reload();
        });
    });


    function changeStatus(checkbox, id) {
        checkbox.checked = !checkbox.checked;
        let url = "{{ route('status_provider', ['id' => ':id']) }}";
        url = url.replace(':id', id);
        Swal.fire({
            title: 'Are you sure to change status of Provider?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes',
            cancelButtonText: 'No'
        }).then((result) => {
            if (result.isConfirmed) {
                checkbox.checked = !checkbox.checked;
                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function (response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message ?? 'Status updated successfully',
                            timer: 2000,
                            showConfirmButton: true
                        });
                    },
                    error: function (xhr) {
                        let title = 'Error';
                        let message = 'Something went wrong!';

                        message = xhr.responseJSON.message;

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
    $(document).ready(function () {

        $('#providerForm').on('submit', function (e) {
            e.preventDefault();

            let serviceId = $('#service_id').val();
            let providerName = $('#provider_name').val();
            let formType = $('#form_type').val();
            let id = $("#edit_provider_id").val();

            let url = "{{ route('add_provider') }}";

            if (formType == 'edit') {
                url = "{{ route('edit_provider', ['id' => ':id']) }}";
                url = url.replace(':id', id);
            }
            $.ajax({
                url: url,
                type: "POST",
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    serviceId: serviceId,
                    providerName: providerName
                },
                success: function (response) {
                    if (response.status) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        $('#providerModal').modal('hide');
                        $('#providerForm')[0].reset();
                        $('#form_type').val('add');
                        $('#edit_provider_id').val('');
                        $('#providerTable').DataTable().ajax.reload(null, false);
                    }
                },
                error: function (xhr) {
                    let message = 'Something went wrong';

                    // Validation errors (Laravel 422)
                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        const errors = xhr.responseJSON.errors;
                        const firstErrorKey = Object.keys(errors)[0];

                        if (firstErrorKey) {
                            message = errors[firstErrorKey][0];
                        }
                    }
                    // Other backend errors
                    else if (xhr.responseJSON?.message) {
                        message = xhr.responseJSON.message;
                    }

                    Swal.fire('Error', message, 'error');
                }

            });
        });
    });

    function openEditService(id, providerName, serviceId) {
        $('#provider_name').val(providerName);
        $('#edit_provider_id').val(id);
        $('#service_id').val(serviceId);
        $('#form_type').val('edit');
        $('#providerModalTitle').text('Edit Provider');
        $('#providerModal').modal('show');
    }
</script>
<script>
    $('#providerModal').on('hidden.bs.modal', function () {
        $('#providerForm')[0].reset();
        $('#form_type').val('add');
        $('#edit_provider_id').val('');
        $('#providerModalTitle').text('Add Provider');
    });
</script>

@endsection